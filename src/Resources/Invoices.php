<?php

declare(strict_types=1);

namespace Dcblogdev\Xero\Resources;

use Dcblogdev\Xero\Enums\FilterOptions;
use Dcblogdev\Xero\Enums\InvoiceStatus;
use Dcblogdev\Xero\Enums\InvoiceType;
use Dcblogdev\Xero\Xero;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class Invoices extends Xero
{
    protected array $queryString = [];

    public function filter(string $key, string|int $value): self
    {
        if (! FilterOptions::isValid($key)) {
            throw new InvalidArgumentException("Filter option '$key' is not valid.");
        }

        $this->queryString[$key] = $value;

        return $this;
    }

    public function get(): array
    {
        $queryString = $this->formatQueryStrings($this->queryString);

        $result = parent::get('Invoices?'.$queryString);

        return $result['body']['Invoices'];
    }

    public function find(string $invoiceId): array
    {
        $result = parent::get('Invoices/'.$invoiceId);

        return $result['body']['Invoices'][0];
    }

    public function onlineUrl(string $invoiceId): string
    {
        $result = parent::get('Invoices/'.$invoiceId.'/OnlineInvoice');

        return $result['body']['OnlineInvoices'][0]['OnlineInvoiceUrl'];
    }

    public function update(string $invoiceId, array $data): array
    {
        $result = parent::post('Invoices/'.$invoiceId, $data);

        return $result['body']['Invoices'][0];
    }

    public function store(array $data): array
    {
        $result = parent::post('Invoices', $data);

        return $result['body']['Invoices'][0];
    }

    public function attachments(string $invoiceId): array
    {
        $result = parent::get('Invoices/'.$invoiceId.'/Attachments');

        return $result['body']['Attachments'];
    }

    public function attachment(string $invoiceId, ?string $attachmentId = null, ?string $fileName = null): string
    {
        // Depending on the application, we may want to get it by the FileName instead of the AttachmentId
        $nameOrId = $attachmentId ? $attachmentId : $fileName;

        $result = parent::get('Invoices/'.$invoiceId.'/Attachments/'.$nameOrId);

        return $result['body'];
    }

    /**
     * Email an invoice to the contact's primary email and any contact persons with IncludeInEmails flag set to true.
     * The invoice must be of Type ACCREC and a valid Status for sending (SUBMITTED, AUTHORISED or PAID).
     *
     * @param  string  $invoiceId  The invoice ID to email
     * @return array Returns an array with status code, success flag, and any messages/errors:
     *               - 'status': HTTP status code (204, 400, etc.)
     *               - 'success': boolean indicating if the email was sent successfully
     *               - 'message': optional success message
     *               - 'errors': optional array of error details
     *               - 'body': optional response body
     *
     * @throws Exception
     */
    public function email(string $invoiceId): array
    {
        try {
            $response = Http::withToken($this->getAccessToken())
                ->withHeaders(['Xero-tenant-id' => $this->getTenantId()])
                ->accept('application/json')
                ->post('https://api.xero.com/api.xro/2.0/Invoices/'.$invoiceId.'/Email', []);

            $statusCode = $response->status();
            $body = $response->json() ?? [];

            // For 204 No Content (success)
            if ($statusCode === 204) {
                return [
                    'status' => 204,
                    'success' => true,
                    'message' => 'Invoice email sent successfully',
                    'body' => [],
                ];
            }

            // For 400 errors, return structured error information
            if ($statusCode === 400) {
                return [
                    'status' => 400,
                    'success' => false,
                    'errors' => $body,
                    'message' => $body['Message'] ?? $body['Detail'] ?? 'Invoice email failed',
                    'body' => $body,
                ];
            }

            // For other errors, throw exception
            $response->throw();

            // This should never be reached, but PHPStan requires a return
            return [];
        } catch (RequestException $e) {
            $statusCode = $e->response->status();
            $body = $e->response->json() ?? [];

            // For 400 errors, return structured error information
            if ($statusCode === 400) {
                return [
                    'status' => 400,
                    'success' => false,
                    'errors' => $body,
                    'message' => $body['Message'] ?? $body['Detail'] ?? 'Invoice email failed',
                    'body' => $body,
                ];
            }

            // For other errors, throw exception as usual
            $response = json_decode($e->response->body());
            throw new Exception($response->Detail ?? "Type: $response?->Type Message: $response?->Message Error Number: $response?->ErrorNumber");
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get the list of email addresses that will receive the invoice email.
     * Returns the primary contact email and any contact persons with IncludeInEmails flag set to true.
     *
     * @param  string  $invoiceId  The invoice ID
     * @return array<string> Array of email addresses
     *
     * @throws Exception
     */
    public function getEmailRecipients(string $invoiceId): array
    {
        $invoice = $this->find($invoiceId);
        $contactId = $invoice['Contact']['ContactID'] ?? null;

        if (! $contactId) {
            return [];
        }

        $contact = $this->contacts()->find($contactId);
        $recipients = [];

        // Add primary contact email if it exists
        if (! empty($contact['EmailAddress'])) {
            $recipients[] = $contact['EmailAddress'];
        }

        // Add contact persons with IncludeInEmails flag set to true
        if (! empty($contact['ContactPersons']) && is_array($contact['ContactPersons'])) {
            foreach ($contact['ContactPersons'] as $contactPerson) {
                if (isset($contactPerson['IncludeInEmails']) && $contactPerson['IncludeInEmails'] === true) {
                    if (! empty($contactPerson['EmailAddress'])) {
                        $recipients[] = $contactPerson['EmailAddress'];
                    }
                }
            }
        }

        return array_unique($recipients);
    }

    /**
     * Check if an invoice can be emailed.
     * The invoice must be of Type ACCREC and have a Status of SUBMITTED, AUTHORISED, or PAID.
     *
     * @param  string  $invoiceId  The invoice ID to check
     * @return bool Returns true if the invoice can be emailed, false otherwise
     *
     * @throws Exception
     */
    public function canEmail(string $invoiceId): bool
    {
        $invoice = $this->find($invoiceId);

        // Invoice must be Type ACCREC
        if (($invoice['Type'] ?? '') !== InvoiceType::AccRec->value) {
            return false;
        }

        // Invoice must have a valid status for sending
        $status = $invoice['Status'] ?? '';
        $validStatuses = [
            InvoiceStatus::Submitted->value,
            InvoiceStatus::Authorised->value,
            InvoiceStatus::Paid->value,
        ];

        return in_array($status, $validStatuses, true);
    }
}
