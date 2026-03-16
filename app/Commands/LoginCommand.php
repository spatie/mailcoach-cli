<?php

namespace App\Commands;

use App\Concerns\RendersBanner;
use App\Services\BrowserOpener;
use App\Services\CredentialStore;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
{
    use RendersBanner;

    protected $signature = 'login';

    protected $description = 'Authenticate with your Mailcoach instance';

    public function handle(CredentialStore $credentials, BrowserOpener $browser): int
    {
        $this->renderBanner();

        $selfHosted = $this->confirm('Are you selfhosting Mailcoach?', false);

        if (! $selfHosted) {
            $teamName = $this->ask('What is your team name?');

            if (! $teamName) {
                $this->error('A team name is required.');

                return self::FAILURE;
            }

            $baseUrl = 'https://'.strtolower($teamName).'.mailcoach.app';
        } else {
            $baseUrl = $this->ask('What is the URL of your Mailcoach instance?');

            if (! $baseUrl) {
                $this->error('A Mailcoach instance URL is required.');

                return self::FAILURE;
            }

            $baseUrl = rtrim($baseUrl, '/');

            if (! str_starts_with($baseUrl, 'http://') && ! str_starts_with($baseUrl, 'https://')) {
                $baseUrl = "https://{$baseUrl}";
            }
        }

        $tokenUrl = "{$baseUrl}/settings/tokens";

        $this->line('');

        if ($this->confirm("Open browser to create an API token at {$tokenUrl}?")) {
            if ($browser->open($tokenUrl)) {
                $this->info('Browser opened. Create a token and paste it below.');
            } else {
                $this->warn("Could not open the browser. Please visit: {$tokenUrl}");
            }
        } else {
            $this->info("Create an API token at: {$tokenUrl}");
        }

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
