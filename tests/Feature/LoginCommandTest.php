<?php

use App\Services\CredentialStore;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir().'/mailcoach-test-'.uniqid();
    $this->configPath = $this->tempDir.'/config.json';

    $store = app(CredentialStore::class);
    $reflection = new ReflectionClass(CredentialStore::class);
    $property = $reflection->getProperty('configPath');
    $property->setAccessible(true);
    $property->setValue($store, $this->configPath);
});

afterEach(function () {
    if (file_exists($this->configPath)) {
        unlink($this->configPath);
    }

    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

it('authenticates with valid credentials', function () {
    Http::fake([
        'https://example.mailcoach.app/api/transactional-mails/templates' => Http::response(['data' => []], 200),
    ]);

    $this->artisan('login')
        ->expectsQuestion('What is the URL of your Mailcoach instance?', 'https://example.mailcoach.app')
        ->expectsQuestion('Paste your API token', 'valid-token-123')
        ->expectsOutputToContain('Successfully authenticated')
        ->assertExitCode(0);

    $store = app(CredentialStore::class);

    expect($store->getToken())->toBe('valid-token-123');
    expect($store->getBaseUrl())->toBe('https://example.mailcoach.app');
});

it('rejects invalid api token', function () {
    Http::fake([
        'https://example.mailcoach.app/api/transactional-mails/templates' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    $this->artisan('login')
        ->expectsQuestion('What is the URL of your Mailcoach instance?', 'https://example.mailcoach.app')
        ->expectsQuestion('Paste your API token', 'invalid-token')
        ->expectsOutputToContain('invalid')
        ->assertExitCode(1);

    $store = app(CredentialStore::class);

    expect($store->getToken())->toBeNull();
});

it('handles connection errors', function () {
    Http::fake([
        'https://unreachable.example.com/api/transactional-mails/templates' => Http::response(null, 500),
    ]);

    $this->artisan('login')
        ->expectsQuestion('What is the URL of your Mailcoach instance?', 'https://unreachable.example.com')
        ->expectsQuestion('Paste your API token', 'some-token')
        ->expectsOutputToContain('unexpected response')
        ->assertExitCode(1);
});
