<?php

namespace Spatie\MailcoachCli;

use Illuminate\Support\ServiceProvider;

class MailcoachSkillsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Boost auto-discovers the resources/boost/ directory
    }
}
