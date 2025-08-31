<?php

namespace Livewire\V4\JsModules;

use Illuminate\Support\Facades\Route;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

class SupportJsModules extends ComponentHook
{
    static function provide()
    {
        Route::get('/livewire/js/{component}.js', function ($component) {
            $component = str_replace('--', '.', $component);

            $instance = app('livewire')->new($component);

            [$source, $filemtime] = $instance->jsModule();

            if ($source === false) {
                throw new \Exception('Component '.$component.' does not have a JS module source.');
            }

            return Utils::pretendResponseIsFileFromString(
                $source,
                $filemtime,
                $component.'.js',
            );
        });
    }

    public function dehydrate($context)
    {
        if (! $context->isMounting()) return;

        if ($this->component->hasJsModule()) {
            $context->addEffect('hasJsModule', true);
        }
    }
}
