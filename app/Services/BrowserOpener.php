<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class BrowserOpener
{
    public function open(string $url): bool
    {
        $command = match (PHP_OS_FAMILY) {
            'Darwin' => ['open', $url],
            'Linux' => ['xdg-open', $url],
            'Windows' => ['cmd', '/c', 'start', '', $url],
            default => null,
        };

        if ($command === null) {
            return false;
        }

        try {
            $process = new Process($command);
            $process->start();

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
