<?php

namespace App\Concerns;

use function Termwind\render;

trait RendersBanner
{
    public function renderBanner(): void
    {
        $version = config('app.version');

        render(<<<HTML
            <div class="mx-2 my-1">
                <div class="text-cyan-500">
                    ╔╦╗╔═╗╦╦  ╔═╗╔═╗╔═╗╔═╗╦ ╦<br/>
                    ║║║╠═╣║║  ║  ║ ║╠═╣║  ╠═╣<br/>
                    ╩ ╩╩ ╩╩╩═╝╚═╝╚═╝╩ ╩╚═╝╩ ╩<br/>
                </div>
                <div class="text-gray mt-1">
                    Mailcoach CLI v{$version}
                </div>
            </div>
        HTML);
    }
}
