<?php

namespace Livewire\Features\SupportJsModules;

use Illuminate\Support\Facades\Route;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

class SupportJsModules extends ComponentHook
{
    static function provide()
    {
        Route::get(EndpointResolver::componentJsPath(), function ($component) {
            $component = str_replace('----', ':', $component);
            $component = str_replace('---', '::', $component);
            $component = str_replace('--', '.', $component);

            $instance = app('livewire')->new($component);

            if (! method_exists($instance, 'scriptModuleSrc')) {
                throw new \Exception('Component '.$component.' does not have a script source.');
            }

            $path = $instance->scriptModuleSrc();

            if (! file_exists($path)) {
                throw new \Exception('Script file not found: '.$path);
            }

            $source = file_get_contents($path);

            $filemtime = filemtime($path);

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

        if (method_exists($this->component, 'scriptModuleSrc')) {
            $path = $this->component->scriptModuleSrc();

            $filemtime = filemtime($path);

            $hash = crc32($filemtime);

            $context->addEffect('scriptModule', $hash);
        }
    }
}
