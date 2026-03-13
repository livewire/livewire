<?php

namespace Livewire\Features\SupportJsModules;

use Illuminate\Support\Facades\Route;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

use function Livewire\store;

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
        // Don't add scriptModule effect during lazy-loading placeholder mount.
        // The component's view isn't rendered yet, so @assets won't have run.
        // The scriptModule will be added when __lazyLoad triggers the real mount.
        if (store($this->component)->get('isLazyLoadMounting') === true) return;

        // Add scriptModule effect during:
        // 1. Normal component mounting ($context->isMounting())
        // 2. Lazy-loaded component hydration (when __lazyLoad runs)
        $isNormalMount = $context->isMounting();
        $isLazyLoadHydration = store($this->component)->get('isLazyLoadHydrating') === true;

        if (! $isNormalMount && ! $isLazyLoadHydration) return;

        if (method_exists($this->component, 'scriptModuleSrc')) {
            $path = $this->component->scriptModuleSrc();

            $filemtime = filemtime($path);

            $hash = crc32($filemtime);

            $context->addEffect('scriptModule', $hash);
        }
    }
}
