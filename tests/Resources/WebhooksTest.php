<?php

declare(strict_types=1);

use Dcblogdev\Xero\Resources\Webhooks;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->webhooks = new Webhooks();
});

// Helper function to mock file_get_contents
function mockFileGetContents($payload)
{
    // Create a stream wrapper to mock php://input
    stream_wrapper_unregister('php');
    stream_wrapper_register('php', MockPhpStream::class);
    file_put_contents('php://input', $payload);
}

// Mock stream wrapper class
class MockPhpStream
{
    protected static $data = '';

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        return true;
    }

    public function stream_read($count)
    {
        $ret = mb_substr(self::$data, 0, $count);
        self::$data = mb_substr(self::$data, $count);

        return $ret;
    }

    public function stream_write($data)
    {
        self::$data = $data;

        return mb_strlen($data);
    }

    public function stream_tell()
    {
        return 0;
    }

    public function stream_eof()
    {
        return true;
    }

    public function stream_stat()
    {
        return [];
    }
}

afterEach(function () {
    // Restore the original stream wrapper
    if (in_array('php', stream_get_wrappers())) {
        stream_wrapper_unregister('php');
        stream_wrapper_restore('php');
    }
});

test('getSignature returns correct signature', function () {
    // Mock the payload
    $payload = json_encode(['events' => [['eventType' => 'test']]]);

    // Use reflection to set protected property
    $reflection = new ReflectionClass($this->webhooks);
    $property = $reflection->getProperty('payload');
    $property->setAccessible(true);
    $property->setValue($this->webhooks, $payload);

    // Set webhook key in config
    Config::set('xero.webhookKey', 'test-webhook-key');

    // Calculate expected signature
    $expectedSignature = base64_encode(hash_hmac('sha256', $payload, 'test-webhook-key', true));

    // Test the method
    expect($this->webhooks->getSignature())->toBe($expectedSignature);
});

test('validate returns true when signatures match', function () {
    // Mock the payload and server variables
    $payload = json_encode(['events' => [['eventType' => 'test']]]);

    // Mock file_get_contents to return our payload
    mockFileGetContents($payload);

    // Set webhook key in config
    Config::set('xero.webhookKey', 'test-webhook-key');

    // Calculate signature
    $signature = base64_encode(hash_hmac('sha256', $payload, 'test-webhook-key', true));

    // Mock $_SERVER
    $_SERVER['HTTP_X_XERO_SIGNATURE'] = $signature;

    // Test the method
    expect($this->webhooks->validate())->toBeTrue();
});

test('validate returns false when signatures do not match', function () {
    // Mock the payload and server variables
    $payload = json_encode(['events' => [['eventType' => 'test']]]);

    // Mock file_get_contents to return our payload
    mockFileGetContents($payload);

    // Set webhook key in config
    Config::set('xero.webhookKey', 'test-webhook-key');

    // Set incorrect signature
    $_SERVER['HTTP_X_XERO_SIGNATURE'] = 'incorrect-signature';

    // Test the method
    expect($this->webhooks->validate())->toBeFalse();
});

test('getEvents returns events from payload', function () {
    // Create a mock of Webhooks with validate method always returning true
    $webhooks = Mockery::mock(Webhooks::class)->makePartial();
    $webhooks->shouldReceive('validate')->andReturn(true);

    // Mock payload with events
    $events = [
        (object) ['eventType' => 'test1', 'resourceId' => '1'],
        (object) ['eventType' => 'test2', 'resourceId' => '2'],
    ];
    $payload = json_encode(['events' => $events]);

    // Use reflection to set protected property
    $reflection = new ReflectionClass($webhooks);
    $property = $reflection->getProperty('payload');
    $property->setAccessible(true);
    $property->setValue($webhooks, $payload);

    // Test the method
    $result = $webhooks->getEvents();

    // Verify each event has the expected properties
    expect($result[0]->eventType)->toBe('test1');
    expect($result[0]->resourceId)->toBe('1');
    expect($result[1]->eventType)->toBe('test2');
    expect($result[1]->resourceId)->toBe('2');
});
