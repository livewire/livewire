<?php

namespace Livewire\Features;

use Illuminate\Support\Facades\Blade;

class SupportLazyLoading
{
    static $shouldRenderLazilly = false;

    public function __invoke()
    {
        Blade::directive('lazy', function ($expression) {
            return <<<'PHP'
                <?php
                    \Livewire\Features\SupportLazyLoading::$shouldRenderLazilly = true;
                ?>
            PHP;
        });

        Blade::directive('endlazy', function ($expression) {
            return <<<'PHP'
                <?php
                    \Livewire\Features\SupportLazyLoading::$shouldRenderLazilly = false;
                ?>
            PHP;
        });

        app('synthetic')->on('mount', function ($name, $params, $parent, $key, $slot, $hijack) {
            if (! static::$shouldRenderLazilly) return;

            $hijack(<<<'HTML'
                <div x-init="">Loading...</div>
            HTML);
        });
    }
}
