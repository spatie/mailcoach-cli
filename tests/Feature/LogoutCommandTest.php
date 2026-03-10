<?php

use App\Services\CredentialStore;

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

it('clears stored credentials', function () {
    $store = app(CredentialStore::class);
    $store->setToken('test-token');
    $store->setBaseUrl('https://example.mailcoach.app');

    expect($store->getToken())->not->toBeNull();

    $this->artisan('logout')
        ->expectsOutputToContain('Credentials have been removed')
        ->assertExitCode(0);

    expect($store->getToken())->toBeNull();
    expect($store->getBaseUrl())->toBeNull();
});
