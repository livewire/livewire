<?php

namespace Livewire\Features\SupportScriptsAndAssets;

use function Livewire\store;

use Livewire\ComponentHook;
use Illuminate\Support\Facades\Blade;

class SupportScriptsAndAssets extends ComponentHook
{
    static function provide()
    {
        Blade::directive('script', function () {
            return <<<PHP

            PHP;
        });

        Blade::directive('assets', function () {
            //
        });
    }

    function dehydrate($context)
    {
        if (! store($this->component)->has('js')) return;

        $context->addEffect('xjs', store($this->component)->get('js'));
    }
}
