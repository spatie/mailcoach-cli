<?php

namespace App\Commands;

use App\Concerns\RendersBanner;
use App\Services\CredentialStore;
use LaravelZero\Framework\Commands\Command;

class LogoutCommand extends Command
{
    use RendersBanner;

    protected $signature = 'logout';

    protected $description = 'Remove stored Mailcoach credentials';

    public function handle(CredentialStore $credentials): int
    {
        $this->renderBanner();

        $credentials->flush();

        $this->info('Credentials have been removed.');

        return self::SUCCESS;
    }
}
