<?php

namespace App\Commands;

use App\Concerns\RendersBanner;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

class ClearCacheCommand extends Command
{
    use RendersBanner;

    protected $signature = 'clear-cache';

    protected $description = 'Clear the cached OpenAPI spec and temp files';

    public function handle(): int
    {
        $this->renderBanner();

        Cache::flush();

        $cachePath = config('cache.stores.file.path');

        if ($cachePath && is_dir($cachePath)) {
            $files = glob("{$cachePath}/*");

            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        $this->info('Cache has been cleared.');

        return self::SUCCESS;
    }
}
