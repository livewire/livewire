<?php

namespace Livewire\Features;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;

class SupportMorphAwareIfStatement
{
    function boot()
    {
        app('livewire')->directive('if', function ($expression) {
            return <<<PHP
            <?php
                ob_start();
                if ($expression) :
            ?>
            PHP;
        });

        app('livewire')->directive('endif', function ($expression) {
            $key = Str::random(6);

            return <<<PHP
            <?php
                echo \Livewire\Features\SupportMorphAwareIfStatement::injectKey(ob_get_clean(), '$key');

                endif;
            ?>
            PHP;
        });
    }

    static function injectKey($content, $key)
    {
        $pattern = '/<[^\/].+?>/xsm';

        $content = preg_replace_callback($pattern, function ($matches) use ($key) {
            $tag = $matches[0];

            $opening = str($tag)->beforeLast('>');
            $closing = '>';

            return $opening.' wire:key="'.$key.'"'.$closing;
        }, $content, limit: 1);

        return $content;
    }
}
