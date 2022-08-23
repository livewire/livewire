<?php

namespace Livewire\Mechanisms;

use function Livewire\invade;
use Livewire\Drawer\IsSingleton;

class HijackBlade
{
    use IsSingleton;

    protected $directives = [];
    protected $renderCounter = 0;

    function boot()
    {
        app('synthetic')->on('render', function ($target, $view) {
            $removals = [];

            if ($this->renderCounter === 0) {
                $customDirectives = app('blade.compiler')->getCustomDirectives();

                foreach ($this->directives as $name => $handler) {
                    if (! isset($customDirectives[$name])) {
                        $customDirectives[$name] = $handler;

                        invade(app('blade.compiler'))->customDirectives = $customDirectives;

                        $removals[] = function () use ($name) {
                            $customDirectives = app('blade.compiler')->getCustomDirectives();

                            unset($customDirectives[$name]);

                            invade(app('blade.compiler'))->customDirectives = $customDirectives;
                        };
                    }
                }
            }

            $this->renderCounter++;

            return function ($html) use ($view, $removals) {
                $this->renderCounter--;

                if ($this->renderCounter === 0) {
                    while ($removals) array_pop($removals)();
                }

                return $html;
            };
        });

        // This is a custom view engine that gets used when rendering
        // Livewire views. Things like letting certain exceptions bubble
        // to the handler, and registering custom directives like: "@this".
        app()->make('view.engine.resolver')->register('blade', function () {
            return $this->getEngine();
        });
    }

    function livewireOnlyDirective($name, $handler)
    {
        $this->directives[$name] = $handler;
    }

    function getEngine()
    {
        return new class(app('blade.compiler')) extends \Illuminate\View\Engines\CompilerEngine {
            //
        };
    }

    function getLivewireCompiler()
    {
        return new class extends \Illuminate\View\Compilers\BladeCompiler {
            //
        };
    }
}
