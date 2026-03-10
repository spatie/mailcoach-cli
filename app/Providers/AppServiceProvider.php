<?php

namespace App\Providers;

use App\Concerns\RendersBanner;
use App\Services\CredentialStore;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\ServiceProvider;
use Spatie\OpenApiCli\Facades\OpenApiCli;

class AppServiceProvider extends ServiceProvider
{
    use RendersBanner;

    public function register(): void
    {
        $this->app->singleton(CredentialStore::class);
    }

    public function boot(): void
    {
        $credentials = app(CredentialStore::class);

        OpenApiCli::register(specPath: 'https://www.mailcoach.app/api-spec/openapi.yaml')
            ->useOperationIds()
            ->cache(ttl: 60 * 60 * 24)
            ->baseUrl(($credentials->getBaseUrl() ?? 'https://your-domain.mailcoach.app').'/api')
            ->auth(fn () => $credentials->getToken())
            ->banner(fn (Command $command) => $this->renderBanner())
            ->onError(function (Response $response, Command $command) {
                if ($response->status() === 401) {
                    $command->error(
                        'Your API token is invalid or expired. Run `mailcoach login` to authenticate.',
                    );

                    return true;
                }

                return false;
            });
    }
}
