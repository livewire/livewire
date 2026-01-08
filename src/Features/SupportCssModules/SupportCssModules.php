<?php

namespace Livewire\Features\SupportCssModules;

use Illuminate\Support\Facades\Route;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

class SupportCssModules extends ComponentHook
{
    static function provide()
    {
        // Route for scoped styles
        Route::get(EndpointResolver::componentCssPath(), function ($component) {
            $component = str_replace('----', ':', $component);
            $component = str_replace('---', '::', $component);
            $component = str_replace('--', '.', $component);

            $instance = app('livewire')->new($component);

            if (! method_exists($instance, 'styleModuleSrc')) {
                throw new \Exception('Component '.$component.' does not have a style source.');
            }

            $path = $instance->styleModuleSrc();

            if (! file_exists($path)) {
                throw new \Exception('Style file not found: '.$path);
            }

            $css = file_get_contents($path);

            // Wrap in component selector for scoping
            $wrappedCss = "[wire\\:name=\"{$component}\"] {\n{$css}\n}";

            $filemtime = filemtime($path);

            return Utils::pretendResponseIsFileFromString(
                $wrappedCss,
                $filemtime,
                $component.'.css',
                'text/css; charset=utf-8',
            );
        });

        // Route for global styles
        Route::get(EndpointResolver::componentGlobalCssPath(), function ($component) {
            $component = str_replace('----', ':', $component);
            $component = str_replace('---', '::', $component);
            $component = str_replace('--', '.', $component);

            $instance = app('livewire')->new($component);

            if (! method_exists($instance, 'globalStyleModuleSrc')) {
                throw new \Exception('Component '.$component.' does not have a global style source.');
            }

            $path = $instance->globalStyleModuleSrc();

            if (! file_exists($path)) {
                throw new \Exception('Global style file not found: '.$path);
            }

            $css = file_get_contents($path);

            $filemtime = filemtime($path);

            return Utils::pretendResponseIsFileFromString(
                $css,
                $filemtime,
                $component.'.global.css',
                'text/css; charset=utf-8',
            );
        });
    }

    public function dehydrate($context)
    {
        if (! $context->isMounting()) return;

        // Add scoped style effect
        if (method_exists($this->component, 'styleModuleSrc')) {
            $path = $this->component->styleModuleSrc();

            $filemtime = filemtime($path);

            $hash = crc32($filemtime);

            $context->addEffect('styleModule', $hash);
        }

        // Add global style effect
        if (method_exists($this->component, 'globalStyleModuleSrc')) {
            $path = $this->component->globalStyleModuleSrc();

            $filemtime = filemtime($path);

            $hash = crc32($filemtime);

            $context->addEffect('globalStyleModule', $hash);
        }
    }
}
