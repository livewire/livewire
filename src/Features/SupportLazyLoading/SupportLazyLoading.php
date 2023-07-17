<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\WithLazyLoading;
use function Livewire\{ on, pipe, wrap };
use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use Livewire\Drawer\Utils;
use Livewire\ComponentHook;
use Livewire\Component;

class SupportLazyLoading extends ComponentHook
{
    static function provide()
    {
        app('livewire')->provide(function () {
            $this->loadViewsFrom(__DIR__.'/views', 'livewire');

            $paths = [__DIR__.'/views' => resource_path('views/vendor/livewire')];

            $this->publishes($paths, 'livewire');
            $this->publishes($paths, 'livewire:lazy-loading');
        });
    }

    public function mount($params)
    {
        $reflectionClass = new \ReflectionClass($this->component);
        $hasLazyProperty = isset($params['lazy']);
        $lazyProperty = $params['lazy'] ?? false;
        $hasLazyAttribute = count($reflectionClass->getAttributes(\Livewire\Attributes\Lazy::class)) > 0;
        $hasLazyTrait = in_array(WithLazyLoading::class, get_declared_traits(), true);

        if ($hasLazyProperty && !$lazyProperty) {
            return;
        }

        if (!$hasLazyProperty && !$hasLazyAttribute && !$hasLazyTrait) {
            return;
        }

        $this->component->skipMount();

        $mountParams = array_diff_key($params, array_flip(['lazy']));

        $this->component->skipRender(
            $this->generatePlaceholderHtml($mountParams)
        );
    }

    public function hydrate($memo)
    {
        if (isset($memo['lazyLoaded'])) return;

        if ($memo['lazyLoaded'] === false) $this->component->skipHydrate();
    }

    function dehydrate($context)
    {
        $context->addMemo('lazyLoaded', false);

        if (! $context->mounting) return;

        $context->addMemo('lazyLoaded', true);
    }


    function call($method, $params, $returnEarly)
    {
        if ($method !== '__lazyLoad') return;

        [ $encoded ] = $params;

        $mountParams = $this->resurrectMountParams($encoded);

        $this->callMountLifecycleMethod($mountParams);

        $returnEarly();
    }

    public function generatePlaceholderHtml($params)
    {
        $this->registerContainerComponent();

        $container = app('livewire')->new('__mountParamsContainer');

        $container->forMount = $params;

        $snapshot = app('livewire')->snapshot($container);

        $encoded = base64_encode(json_encode($snapshot));

        $placeholder = wrap($this->component)
            ->withFallback(view(config('livewire.lazy_loading_placeholder'))->render())
            ->placeholder();

        $html = Utils::insertAttributesIntoHtmlRoot($placeholder, [
            'x-intersect' => '$wire.__lazyLoad(\''.$encoded.'\')',
        ]);

        return $html;
    }

    function resurrectMountParams($encoded)
    {
        $snapshot = json_decode(base64_decode($encoded), associative: true);

        $this->registerContainerComponent();

        [ $container ] = app('livewire')->fromSnapshot($snapshot);

        return $container->forMount;
    }

    function callMountLifecycleMethod($params)
    {
        $hook = new SupportLifecycleHooks;

        $hook->setComponent($this->component);

        $hook->mount($params);
    }

    public function registerContainerComponent()
    {
        app('livewire')->component('__mountParamsContainer', new class extends Component {
            public $forMount;
        });
    }
}
