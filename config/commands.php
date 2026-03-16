<?php

use Illuminate\Foundation\Console\VendorPublishCommand;
use NunoMaduro\LaravelConsoleSummary\SummaryCommand;
use Symfony\Component\Console\Command\DumpCompletionCommand;
use Symfony\Component\Console\Command\HelpCommand;

return [
    'default' => SummaryCommand::class,
    'paths' => [app_path('Commands')],
    'add' => [],
    'hidden' => [
        SummaryCommand::class,
        DumpCompletionCommand::class,
        HelpCommand::class,
        VendorPublishCommand::class,
    ],
    'remove' => [],
];
