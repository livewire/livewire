<?php

namespace Livewire\V4\Placeholders;

use Livewire\V4\Placeholders\PlaceholderCompiler;
use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

class SupportPlaceholders extends ComponentHook
{
    static function provide()
    {
        Blade::precompiler(function ($content) {
            $path = Blade::getPath();

            return (new PlaceholderCompiler)->compile($content, $path);
        });
    }
}
