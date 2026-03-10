<?php

return [
    'name' => 'Mailcoach',
    'version' => '1.0.0',
    'env' => 'production',
    'providers' => [
        App\Providers\AppServiceProvider::class,
        Spatie\OpenApiCli\OpenApiCliServiceProvider::class,
    ],
];
