<?php

namespace Dcblogdev\Xero\Models;

use DateTimeInterface;
use Dcblogdev\Xero\database\factories\TokenFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $tenant_name
 * @property string $access_token
 * @property string $refresh_token
 * @property int $expires_in
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property mixed $expires
 */
class XeroToken extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): TokenFactory
    {
        return TokenFactory::new();
    }

    /**
     * @return Attribute<Carbon, never>
     */
    protected function expires(): Attribute
    {
        return Attribute::get(
            fn (): DateTimeInterface => $this->updated_at->addSeconds((int) $this->expires_in)
        );
    }

    protected $casts = [
        'expires_in' => 'integer',
    ];
}
