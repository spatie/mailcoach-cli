<?php

it('clears the cache', function () {
    $cachePath = config('cache.stores.file.path');

    if (! is_dir($cachePath)) {
        mkdir($cachePath, 0755, true);
    }

    file_put_contents("{$cachePath}/test-cache-file", 'cached content');

    $this->artisan('clear-cache')
        ->expectsOutputToContain('Cache has been cleared')
        ->assertExitCode(0);

    expect(file_exists("{$cachePath}/test-cache-file"))->toBeFalse();
});
