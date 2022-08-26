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
            return <<<PHP
            <?php
                endif;
                echo \Livewire\Features\SupportMorphAwareIfStatement::injectMarkers(ob_get_clean());
            ?>
            PHP;
        });
    }

    static function injectMarkers($content)
    {
        if (str_starts_with(trim($content, " \n"), '<')) {
            return '<!-- __IF__ -->'.$content.'<!-- __ENDIF__ -->';
        }

        return $content;
    }
}
