<?php

use App\Services\BrowserOpener;
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

it('authenticates cloud users with team name', function () {
    Http::fake([
        'https://veedee.mailcoach.app/api/transactional-mails/templates' => Http::response(['data' => []], 200),
    ]);

    $this->artisan('login')
        ->expectsConfirmation('Are you selfhosting Mailcoach?', 'no')
        ->expectsQuestion('What is your team name?', 'Veedee')
        ->expectsConfirmation('Open browser to create an API token at https://veedee.mailcoach.app/settings/tokens?', 'no')
        ->expectsQuestion('Paste your API token', 'valid-token-123')
        ->expectsOutputToContain('Successfully authenticated')
        ->assertExitCode(0);

    $store = app(CredentialStore::class);

    expect($store->getToken())->toBe('valid-token-123');
    expect($store->getBaseUrl())->toBe('https://veedee.mailcoach.app');
});

it('authenticates self-hosted users with full url', function () {
    Http::fake([
        'https://example.mailcoach.app/api/transactional-mails/templates' => Http::response(['data' => []], 200),
    ]);

    $this->artisan('login')
        ->expectsConfirmation('Are you selfhosting Mailcoach?', 'yes')
        ->expectsQuestion('What is the URL of your Mailcoach instance?', 'https://example.mailcoach.app')
        ->expectsConfirmation('Open browser to create an API token at https://example.mailcoach.app/settings/tokens?', 'no')
        ->expectsQuestion('Paste your API token', 'valid-token-123')
        ->expectsOutputToContain('Successfully authenticated')
        ->assertExitCode(0);

    $store = app(CredentialStore::class);

    expect($store->getToken())->toBe('valid-token-123');
    expect($store->getBaseUrl())->toBe('https://example.mailcoach.app');
});

it('prepends https for self-hosted urls without scheme', function () {
    Http::fake([
        'https://mailcoach.example.com/api/transactional-mails/templates' => Http::response(['data' => []], 200),
    ]);

    $this->artisan('login')
        ->expectsConfirmation('Are you selfhosting Mailcoach?', 'yes')
        ->expectsQuestion('What is the URL of your Mailcoach instance?', 'mailcoach.example.com')
        ->expectsConfirmation('Open browser to create an API token at https://mailcoach.example.com/settings/tokens?', 'no')
        ->expectsQuestion('Paste your API token', 'valid-token-123')
        ->expectsOutputToContain('Successfully authenticated')
        ->assertExitCode(0);

    $store = app(CredentialStore::class);

    expect($store->getBaseUrl())->toBe('https://mailcoach.example.com');
});

it('rejects invalid api token', function () {
    Http::fake([
        'https://veedee.mailcoach.app/api/transactional-mails/templates' => Http::response(['message' => 'Unauthenticated.'], 401),
    ]);

    $this->artisan('login')
        ->expectsConfirmation('Are you selfhosting Mailcoach?', 'no')
        ->expectsQuestion('What is your team name?', 'Veedee')
        ->expectsConfirmation('Open browser to create an API token at https://veedee.mailcoach.app/settings/tokens?', 'no')
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
        ->expectsConfirmation('Are you selfhosting Mailcoach?', 'yes')
        ->expectsQuestion('What is the URL of your Mailcoach instance?', 'https://unreachable.example.com')
        ->expectsConfirmation('Open browser to create an API token at https://unreachable.example.com/settings/tokens?', 'no')
        ->expectsQuestion('Paste your API token', 'some-token')
        ->expectsOutputToContain('unexpected response')
        ->assertExitCode(1);
});

it('opens browser when user confirms', function () {
    Http::fake([
        'https://veedee.mailcoach.app/api/transactional-mails/templates' => Http::response(['data' => []], 200),
    ]);

    $browserMock = Mockery::mock(BrowserOpener::class);
    $browserMock->shouldReceive('open')
        ->once()
        ->with('https://veedee.mailcoach.app/settings/tokens')
        ->andReturn(true);

    $this->app->instance(BrowserOpener::class, $browserMock);

    $this->artisan('login')
        ->expectsConfirmation('Are you selfhosting Mailcoach?', 'no')
        ->expectsQuestion('What is your team name?', 'Veedee')
        ->expectsConfirmation('Open browser to create an API token at https://veedee.mailcoach.app/settings/tokens?', 'yes')
        ->expectsOutputToContain('Browser opened')
        ->expectsQuestion('Paste your API token', 'valid-token-123')
        ->expectsOutputToContain('Successfully authenticated')
        ->assertExitCode(0);
});

it('shows url when browser fails to open', function () {
    Http::fake([
        'https://veedee.mailcoach.app/api/transactional-mails/templates' => Http::response(['data' => []], 200),
    ]);

    $browserMock = Mockery::mock(BrowserOpener::class);
    $browserMock->shouldReceive('open')
        ->once()
        ->andReturn(false);

    $this->app->instance(BrowserOpener::class, $browserMock);

    $this->artisan('login')
        ->expectsConfirmation('Are you selfhosting Mailcoach?', 'no')
        ->expectsQuestion('What is your team name?', 'Veedee')
        ->expectsConfirmation('Open browser to create an API token at https://veedee.mailcoach.app/settings/tokens?', 'yes')
        ->expectsOutputToContain('Could not open the browser')
        ->expectsQuestion('Paste your API token', 'valid-token-123')
        ->expectsOutputToContain('Successfully authenticated')
        ->assertExitCode(0);
});
