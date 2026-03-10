<?php

namespace App\Commands;

use App\Concerns\RendersBanner;
use App\Services\CredentialStore;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
{
    use RendersBanner;

    protected $signature = 'login';

    protected $description = 'Authenticate with your Mailcoach instance';

    public function handle(CredentialStore $credentials): int
    {
        $this->renderBanner();

        $baseUrl = $this->ask('What is the URL of your Mailcoach instance?');

        if (! $baseUrl) {
            $this->error('A Mailcoach instance URL is required.');

            return self::FAILURE;
        }

        $baseUrl = rtrim($baseUrl, '/');

        $this->line('');
        $this->info("Create an API token at: {$baseUrl}/account/api-tokens");
        $this->line('');

        $token = $this->secret('Paste your API token');

        if (! $token) {
            $this->error('An API token is required.');

            return self::FAILURE;
        }

        $this->line('');
        $this->comment('Verifying credentials...');

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$baseUrl}/api/transactional-mails/templates");
        } catch (\Exception $e) {
            $this->error("Could not connect to {$baseUrl}. Please check the URL and try again.");

            return self::FAILURE;
        }

        if ($response->status() === 401) {
            $this->error('The API token is invalid. Please check your token and try again.');

            return self::FAILURE;
        }

        if (! $response->successful()) {
            $this->error("Received an unexpected response (HTTP {$response->status()}) from {$baseUrl}.");

            return self::FAILURE;
        }

        $credentials->setBaseUrl($baseUrl);
        $credentials->setToken($token);

        $this->info('Successfully authenticated with Mailcoach!');

        return self::SUCCESS;
    }
}
