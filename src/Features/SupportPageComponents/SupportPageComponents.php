<?php

namespace Livewire\Features\SupportPageComponents;

use function Livewire\{on, off, once};
use Livewire\Drawer\ImplicitRouteBinding;
use Livewire\ComponentHook;
use Illuminate\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupportPageComponents extends ComponentHook
{
    static function provide()
    {
        static::registerLayoutViewMacros();

        static::resolvePageComponentRouteBindings();
    }

    static function registerLayoutViewMacros()
    {
        View::macro('layoutData', function ($data = []) {
            if (! isset($this->layoutConfig)) $this->layoutConfig = new PageComponentConfig;

            $this->layoutConfig->mergeParams($data);

            return $this;
        });

        View::macro('section', function ($section) {
            if (! isset($this->layoutConfig)) $this->layoutConfig = new PageComponentConfig;

            $this->layoutConfig->slotOrSection = $section;

            return $this;
        });

        View::macro('title', function ($title) {
            if (! isset($this->layoutConfig)) $this->layoutConfig = new PageComponentConfig;

            $this->layoutConfig->mergeParams(['title' => $title]);

            return $this;
        });

        View::macro('slot', function ($slot) {
            if (! isset($this->layoutConfig)) $this->layoutConfig = new PageComponentConfig;

            $this->layoutConfig->slotOrSection = $slot;

            return $this;
        });

        View::macro('extends', function ($view, $params = []) {
            if (! isset($this->layoutConfig)) $this->layoutConfig = new PageComponentConfig;

            $this->layoutConfig->type = 'extends';
            $this->layoutConfig->slotOrSection = 'content';
            $this->layoutConfig->view = $view;
            $this->layoutConfig->mergeParams($params);

            return $this;
        });

        View::macro('layout', function ($view, $params = []) {
            if (! isset($this->layoutConfig)) $this->layoutConfig = new PageComponentConfig;

            $this->layoutConfig->type = 'component';
            $this->layoutConfig->slotOrSection = 'slot';
            $this->layoutConfig->view = $view;
            $this->layoutConfig->mergeParams($params);

            return $this;
        });

        View::macro('response', function (callable $callback) {
            if (! isset($this->layoutConfig)) $this->layoutConfig = new PageComponentConfig;

            $this->layoutConfig->response = $callback;

            return $this;
        });
    }

    static function interceptTheRenderOfTheComponentAndRetreiveTheLayoutConfiguration($callback)
    {
        $layoutConfig = null;
        $slots = [];

        // Only run this handler once for the parent-most component. Otherwise child components
        // will run this handler too and override the configured layout...
        $handler = once(function ($target, $view, $data) use (&$layoutConfig, &$slots) {
            $layoutAttr = $target->getAttributes()->whereInstanceOf(BaseLayout::class)->first();
            $titleAttr = $target->getAttributes()->whereInstanceOf(BaseTitle::class)->first();

            if ($layoutAttr) {
                $view->layout($layoutAttr->name, $layoutAttr->params);
            }

            if ($titleAttr) {
                $view->title($titleAttr->content);
            }

            $layoutConfig = $view->layoutConfig ?? new PageComponentConfig;

            return function ($html, $replace, $viewContext) use ($view, $layoutConfig) {
                // Gather up any slots and sections declared in the component template and store them
                // to be later forwarded into the layout component itself...
                $layoutConfig->viewContext = $viewContext;
            };
        });

        on('render', $handler);
        on('render.placeholder', $handler);

        $callback();

        off('render', $handler);
        off('render.placeholder', $handler);

        return $layoutConfig;
    }

    static function gatherMountMethodParamsFromRouteParameters($component)
    {
        // This allows for route parameters like "slug" in /post/{slug},
        // to be passed into a Livewire component's mount method...
        $route = request()->route();

        if (! $route) return [];

        try {
            $params = (new ImplicitRouteBinding(app()))
                ->resolveAllParameters($route, new $component);
        } catch (ModelNotFoundException $exception) {
            if (method_exists($route,'getMissing') && $route->getMissing()) {
                abort(
                    $route->getMissing()(request())
                );
            }

            throw $exception;
        }

        return $params;
    }

    static function renderContentsIntoLayout($content, $layoutConfig)
    {
        try {
            if ($layoutConfig->type === 'component') {
                return Blade::render(<<<'HTML'
                    <?php $layout->viewContext->mergeIntoNewEnvironment($__env); ?>

                    @component($layout->view, $layout->params)
                        @slot($layout->slotOrSection)
                            {!! $content !!}
                        @endslot

                        <?php
                        // Manually forward slots defined in the Livewire template into the layout component...
                        foreach ($layout->viewContext->slots[-1] ?? [] as $name => $slot) {
                            $__env->slot($name, attributes: $slot->attributes->getAttributes());
                            echo $slot->toHtml();
                            $__env->endSlot();
                        }
                        ?>
                    @endcomponent
                HTML, [
                    'content' => $content,
                    'layout' => $layoutConfig,
                ]);
            } else {
                return Blade::render(<<<'HTML'
                    <?php $layout->viewContext->mergeIntoNewEnvironment($__env); ?>

                    @extends($layout->view, $layout->params)

                    @section($layout->slotOrSection)
                        {!! $content !!}
                    @endsection
                HTML, [
                    'content' => $content,
                    'layout' => $layoutConfig,
                ]);
            }
        } catch (\Illuminate\View\ViewException $e) {
            $layout = $layoutConfig->view;

            if (str($e->getMessage())->startsWith('View ['.$layout.'] not found.')) {
                throw new MissingLayoutException($layout);
            } else {
                throw $e;
            }
        }
    }

    // This needs to exist so that authorization middleware (and other middleware) have access
    // to implicit route bindings based on the Livewire page component. Otherwise, Laravel
    // has no implicit binding references because the action is __invoke with no params
    protected static function resolvePageComponentRouteBindings()
    {
        // This method was introduced into Laravel 10.37.1 for this exact purpose...
        if (static::canSubstituteImplicitBindings()) {
            app('router')->substituteImplicitBindingsUsing(function ($container, $route, $default) {
                // If the current route is a Livewire page component...
                if ($componentClass = static::routeActionIsAPageComponent($route)) {
                    // Resolve and set all page component parameters to the current route...
                    (new \Livewire\Drawer\ImplicitRouteBinding($container))
                        ->resolveAllParameters($route, new $componentClass);
                } else {
                    // Otherwise, run the default Laravel implicit binding system...
                    $default();
                }
            });
        }
    }

    public static function canSubstituteImplicitBindings()
    {
        return method_exists(app('router'), 'substituteImplicitBindingsUsing');
    }

    protected static function routeActionIsAPageComponent($route)
    {
        $action = $route->action;

        if (! $action) return false;

        $uses = $action['uses'] ?? false;

        if (! $uses) return;

        if (is_string($uses)) {
            $class = str($uses)->before('@')->toString();
            $method = str($uses)->after('@')->toString();

            if (is_subclass_of($class, \Livewire\Component::class) && $method === '__invoke') {
                return $class;
            }
        }

        return false;
    }
}
