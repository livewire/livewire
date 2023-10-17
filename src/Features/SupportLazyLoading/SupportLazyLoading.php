<?php

namespace Livewire\Features\SupportLazyLoading;

use Illuminate\Routing\Route;
use Livewire\Component;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;

use function Livewire\store;
use function Livewire\wrap;

class SupportLazyLoading extends ComponentHook
{
    public static function provide()
    {
        static::registerRouteMacro();
    }

    public static function registerRouteMacro()
    {
        Route::macro('lazy', function ($enabled = true) {
            $this->defaults['lazy'] = $enabled;

            return $this;
        });
    }

    public function mount($params)
    {
        $hasLazyParam = isset($params['lazy']);
        $lazyProperty = $params['lazy'] ?? false;

        $reflectionClass = new \ReflectionClass($this->component);
        $hasLazyAttribute = count($reflectionClass->getAttributes(\Livewire\Attributes\Lazy::class)) > 0;

        // If `:lazy="false"` disable lazy loading...
        if ($hasLazyParam && ! $lazyProperty) {
            return;
        }
        // If no lazy loading is included at all...
        if (! $hasLazyParam && ! $hasLazyAttribute) {
            return;
        }

        $this->component->skipMount();

        store($this->component)->set('isLazyLoadMounting', true);

        $this->component->skipRender(
            $this->generatePlaceholderHtml($params)
        );
    }

    public function hydrate($memo)
    {
        if (! isset($memo['lazyLoaded'])) {
            return;
        }
        if ($memo['lazyLoaded'] === true) {
            return;
        }

        $this->component->skipHydrate();

        store($this->component)->set('isLazyLoadHydrating', true);
    }

    public function dehydrate($context)
    {
        if (store($this->component)->get('isLazyLoadMounting') === true) {
            $context->addMemo('lazyLoaded', false);
        } elseif (store($this->component)->get('isLazyLoadHydrating') === true) {
            $context->addMemo('lazyLoaded', true);
        }
    }

    public function call($method, $params, $returnEarly)
    {
        if ($method !== '__lazyLoad') {
            return;
        }

        [$encoded] = $params;

        $mountParams = $this->resurrectMountParams($encoded);

        $this->callMountLifecycleMethod($mountParams);

        $returnEarly();
    }

    public function generatePlaceholderHtml($params)
    {
        $this->registerContainerComponent();

        $container = app('livewire')->new('__mountParamsContainer');

        $container->forMount = array_diff_key($params, array_flip(['lazy']));

        $snapshot = app('livewire')->snapshot($container);

        $encoded = base64_encode(json_encode($snapshot));

        $globalPlaceholder = config('livewire.lazy_placeholder');

        $placeholderHtml = $globalPlaceholder
            ? view($globalPlaceholder)->render()
            : '<div></div>';

        $placeholder = wrap($this->component)
            ->withFallback($placeholderHtml)
            ->placeholder($params);

        $html = Utils::insertAttributesIntoHtmlRoot($placeholder, [
            ((isset($params['lazy']) and $params['lazy'] === 'on-load') ? 'x-init' : 'x-intersect') => '$wire.__lazyLoad(\''.$encoded.'\')',
        ]);

        return $html;
    }

    public function resurrectMountParams($encoded)
    {
        $snapshot = json_decode(base64_decode($encoded), associative: true);

        $this->registerContainerComponent();

        [$container] = app('livewire')->fromSnapshot($snapshot);

        return $container->forMount;
    }

    public function callMountLifecycleMethod($params)
    {
        $hook = new SupportLifecycleHooks;

        $hook->setComponent($this->component);

        $hook->mount($params);
    }

    public function registerContainerComponent()
    {
        app('livewire')->component('__mountParamsContainer', new class extends Component
        {
            public $forMount;
        });
    }
}
