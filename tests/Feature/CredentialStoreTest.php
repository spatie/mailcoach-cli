<?php

use App\Services\CredentialStore;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir().'/mailcoach-test-'.uniqid();
    $this->configPath = $this->tempDir.'/config.json';

    $this->store = new class($this->configPath) extends CredentialStore
    {
        public function __construct(private string $testConfigPath)
        {
            parent::__construct();
        }

        public function getConfigPath(): string
        {
            return $this->testConfigPath;
        }
    };

    // Use reflection to set the private configPath
    $reflection = new ReflectionClass(CredentialStore::class);
    $property = $reflection->getProperty('configPath');
    $property->setAccessible(true);
    $property->setValue($this->store, $this->configPath);
});

afterEach(function () {
    if (file_exists($this->configPath)) {
        unlink($this->configPath);
    }

    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

it('stores and retrieves a token', function () {
    $this->store->setToken('test-token-123');

    expect($this->store->getToken())->toBe('test-token-123');
});

it('stores and retrieves a base url', function () {
    $this->store->setBaseUrl('https://example.mailcoach.app');

    expect($this->store->getBaseUrl())->toBe('https://example.mailcoach.app');
});

it('strips trailing slash from base url', function () {
    $this->store->setBaseUrl('https://example.mailcoach.app/');

    expect($this->store->getBaseUrl())->toBe('https://example.mailcoach.app');
});

it('returns null when no token is set', function () {
    expect($this->store->getToken())->toBeNull();
});

it('returns null when no base url is set', function () {
    expect($this->store->getBaseUrl())->toBeNull();
});

it('creates the config directory if it does not exist', function () {
    expect(is_dir($this->tempDir))->toBeFalse();

    $this->store->setToken('test-token');

    expect(is_dir($this->tempDir))->toBeTrue();
    expect(file_exists($this->configPath))->toBeTrue();
});

it('flushes credentials', function () {
    $this->store->setToken('test-token');
    $this->store->setBaseUrl('https://example.mailcoach.app');

    expect(file_exists($this->configPath))->toBeTrue();

    $this->store->flush();

    expect(file_exists($this->configPath))->toBeFalse();
    expect($this->store->getToken())->toBeNull();
    expect($this->store->getBaseUrl())->toBeNull();
});

it('persists both token and base url together', function () {
    $this->store->setToken('my-token');
    $this->store->setBaseUrl('https://my-instance.mailcoach.app');

    $contents = json_decode(file_get_contents($this->configPath), true);

    expect($contents)->toBe([
        'token' => 'my-token',
        'base_url' => 'https://my-instance.mailcoach.app',
    ]);
});
