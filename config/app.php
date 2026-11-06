<?php

use App\Providers\AppServiceProvider;
use Spatie\OpenApiCli\OpenApiCliServiceProvider;

return [
    'name' => 'Mailcoach',
    'version' => '1.0.2',
    'env' => 'production',
    'providers' => [
        AppServiceProvider::class,
        OpenApiCliServiceProvider::class,
    ],
];
