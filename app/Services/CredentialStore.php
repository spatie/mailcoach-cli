<?php

namespace App\Services;

class CredentialStore
{
    private string $configPath;

    public function __construct()
    {
        $configDir = ($_SERVER['HOME'] ?? $_SERVER['USERPROFILE'] ?? '~').'/.mailcoach';
        $this->configPath = $configDir.'/config.json';
    }

    public function getToken(): ?string
    {
        return $this->read()['token'] ?? null;
    }

    public function setToken(string $token): void
    {
        $config = $this->read();
        $config['token'] = $token;

        $this->write($config);
    }

    public function getBaseUrl(): ?string
    {
        return $this->read()['base_url'] ?? null;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $config = $this->read();
        $config['base_url'] = rtrim($baseUrl, '/');

        $this->write($config);
    }

    public function flush(): void
    {
        if (file_exists($this->configPath)) {
            unlink($this->configPath);
        }
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /** @return array<string, string> */
    private function read(): array
    {
        if (! file_exists($this->configPath)) {
            return [];
        }

        $contents = file_get_contents($this->configPath);

        if ($contents === false) {
            return [];
        }

        return json_decode($contents, true) ?? [];
    }

    /** @param array<string, string> $config */
    private function write(array $config): void
    {
        $dir = dirname($this->configPath);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $this->configPath,
            json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }
}
