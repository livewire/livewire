<?php

namespace Livewire\Features\SupportJsModules;

use Illuminate\Support\Facades\Route;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

use function Livewire\on;

class SupportJsModules extends ComponentHook
{
    protected static $modulesMountedThisRequest = [];

    static function provide()
    {
        on('flush-state', function () {
            static::$modulesMountedThisRequest = [];
        });

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
        // The placeholder mount of a lazy component doesn't get a scriptModule
        // effect (its view hasn't rendered yet), but the full-page render can
        // still start the module's fetch from the head so it's warm by the
        // time the real mount arrives...
        if ($this->storeGet('isLazyLoadMounting') === true) {
            $this->registerModulePreloadAsset();

            return;
        }

        // Add scriptModule effect during:
        // 1. Normal component mounting ($context->isMounting())
        // 2. Lazy-loaded component hydration (when __lazyLoad runs)
        $isNormalMount = $context->isMounting();
        $isLazyLoadHydration = $this->storeGet('isLazyLoadHydrating') === true;

        if (! $isNormalMount && ! $isLazyLoadHydration) {
            // This component is being updated rather than mounted. If any
            // children with script modules mounted during this request, tell
            // the client now so it can start fetching their modules before
            // the morph that adds them to the page...
            $this->flushModulesMountedThisRequest($context);

            return;
        }

        if (method_exists($this->component, 'scriptModuleSrc')) {
            $path = $this->component->scriptModuleSrc();

            $filemtime = filemtime($path);

            $hash = crc32($filemtime);

            $context->addEffect('scriptModule', $hash);

            if ($isNormalMount) {
                // On an initial page load, a modulepreload link in the head
                // starts the fetch during HTML parsing — long before Livewire
                // boots and discovers the module itself...
                $this->registerModulePreloadAsset();

                // During an update request, the mount belongs to a new child —
                // collect it so the update's root component can announce it...
                static::$modulesMountedThisRequest[$this->component->getName()] = $hash;
            }
        }

        // A hydrating lazy component's children mount during its render —
        // announce their modules the same way an updating component does...
        if ($isLazyLoadHydration) {
            $this->flushModulesMountedThisRequest($context);
        }
    }

    protected function registerModulePreloadAsset()
    {
        // Preloading is for full-page renders, where the link lands in the
        // head during HTML parsing. Rendered assets also ride along in update
        // payloads, so skip update requests entirely — new children there are
        // announced through the childScriptModules effect instead...
        if (app('livewire')->isLivewireRequest()) return;

        if (! method_exists($this->component, 'scriptModuleSrc')) return;

        $path = $this->component->scriptModuleSrc();

        if (! file_exists($path)) return;

        $hash = crc32(filemtime($path));

        $url = static::moduleUrl($this->component->getName(), $hash);

        SupportScriptsAndAssets::$renderedAssets['module-preload:'.$url] = '<link rel="modulepreload" href="'.$url.'">';
    }

    protected function flushModulesMountedThisRequest($context)
    {
        if (empty(static::$modulesMountedThisRequest)) return;

        $modules = [];

        foreach (static::$modulesMountedThisRequest as $name => $hash) {
            $modules[] = ['name' => $name, 'hash' => $hash];
        }

        static::$modulesMountedThisRequest = [];

        $context->addEffect('childScriptModules', $modules);
    }

    public static function moduleUrl($name, $hash)
    {
        $encodedName = str_replace('.', '--', $name);
        $encodedName = str_replace('::', '---', $encodedName);
        $encodedName = str_replace(':', '----', $encodedName);

        return url(app('livewire')->getUriPrefix()).'/js/'.$encodedName.'.js?v='.$hash;
    }
}
