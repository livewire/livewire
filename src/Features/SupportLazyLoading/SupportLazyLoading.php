<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Livewire\Mechanisms\HandleComponents\ViewContext;
use function Livewire\{ on, store, trigger, wrap };
use Illuminate\Routing\Route;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Component;

class SupportLazyLoading extends ComponentHook
{
    protected const MOUNT_PARAMS_CONTAINER = '__mountParamsContainer';

    static $disableWhileTesting = false;

    static function disableWhileTesting()
    {
        static::$disableWhileTesting = true;
    }

    static function provide()
    {
        static::registerRouteMacro();

        on('flush-state', function () {
            static::$disableWhileTesting = false;
        });
    }

    static function registerRouteMacro()
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
        $isolate = true;

        $reflectionClass = new \ReflectionClass($this->component);
        $lazyAttribute = $reflectionClass->getAttributes(\Livewire\Attributes\Lazy::class)[0] ?? null;

        // If Livewire::withoutLazyLoading()...
        if (static::$disableWhileTesting) return;
        // If `:lazy="false"` disable lazy loading...
        if ($hasLazyParam && ! $lazyProperty) return;
        // If no lazy loading is included at all...
        if (! $hasLazyParam && ! $lazyAttribute) return;

        if ($lazyAttribute) {
            $attribute = $lazyAttribute->newInstance();

            $isolate = $attribute->isolate;
        }

        $this->component->skipMount();

        $this->storeSet('isLazyLoadMounting', true);
        $this->storeSet('isLazyIsolated', $isolate);

        $this->component->skipRender(
            $this->generatePlaceholderHtml($params)
        );
    }

    public function hydrate($memo)
    {
        if (! isset($memo['lazyLoaded'])) return;
        if ($memo['lazyLoaded'] === true) return;

        $this->component->skipHydrate();

        $this->storeSet('isLazyLoadHydrating', true);
    }

    function dehydrate($context)
    {
        if ($this->storeGet('isLazyLoadMounting') === true) {
            $context->addMemo('lazyLoaded', false);
            $context->addMemo('lazyIsolated', $this->storeGet('isLazyIsolated'));
        } elseif ($this->storeGet('isLazyLoadHydrating') === true) {
            $context->addMemo('lazyLoaded', true);
        }
    }

    // Resume a deferred lazy load once the browser requests it.
    function call($method, $params, $returnEarly)
    {
        if ($method !== '__lazyLoad') return;

        // Only applies while a lazy load is being resumed.
        if ($this->storeGet('isLazyLoadHydrating') !== true) return;

        [ $encoded ] = $params;

        $mountParams = $this->resurrectMountParams($encoded);

        $this->callMountLifecycleMethod($mountParams);

        $returnEarly();
    }

    // Render the placeholder and capture the mount params for the resume request.
    public function generatePlaceholderHtml($params)
    {
        $this->registerContainerComponent();

        $container = app('livewire')->new(static::MOUNT_PARAMS_CONTAINER);

        $container->forMount = array_diff_key($params, array_flip(['lazy']));

        // Remember which component these params belong to.
        $container->forComponent = $this->component->getName();

        $context = new ComponentContext($container, mounting: true);

        trigger('dehydrate', $container, $context);

        $snapshot = app('livewire')->snapshot($container, $context);

        $encoded = base64_encode(json_encode($snapshot));

        $placeholder = $this->getPlaceholderView($this->component, $params);

        $finish = trigger('render.placeholder', $this->component, $placeholder, $params);

        $viewContext = new ViewContext;

        $html = $placeholder->render(function ($view) use ($viewContext) {
            // Extract leftover slots, sections, and pushes before they get flushed...
            $viewContext->extractFromEnvironment($view->getFactory());
        });

        $html = Utils::insertAttributesIntoHtmlRoot($html, [
            ((isset($params['lazy']) and $params['lazy'] === 'on-load') ? 'x-init' : 'x-intersect') => '$wire.__lazyLoad(\''.$encoded.'\')',
        ]);

        $replaceHtml = function ($newHtml) use (&$html) {
            $html = $newHtml;
        };

        $html = $finish($html, $replaceHtml, $viewContext);

        return $html;
    }

    protected function getPlaceholderView($component, $params)
    {
        $globalPlaceholder = config('livewire.lazy_placeholder');

        $placeholderHtml = $globalPlaceholder
            ? view($globalPlaceholder)->render()
            : '<div></div>';

        $viewOrString = wrap($component)->withFallback($placeholderHtml)->placeholder($params);

        $properties = Utils::getPublicPropertiesDefinedOnSubclass($component);

        $view = Utils::generateBladeView($viewOrString, $properties);

        return $view;
    }

    // Rebuild the captured mount params from the container snapshot.
    function resurrectMountParams($encoded)
    {
        $snapshot = json_decode(base64_decode($encoded), associative: true);

        $this->registerContainerComponent();

        [ $container ] = app('livewire')->fromSnapshot($snapshot);

        if ($container->forComponent !== $this->component->getName()) {
            throw new CorruptComponentPayloadException;
        }

        return $container->forMount;
    }

    function callMountLifecycleMethod($params)
    {
        $hook = new SupportLifecycleHooks;

        $hook->setComponent($this->component);

        $hook->mount($params);
    }

    // A throwaway component used to carry mount params across the round-trip.
    public function registerContainerComponent()
    {
        app('livewire')->component(static::MOUNT_PARAMS_CONTAINER, new class extends Component {
            public $forMount;
            public $forComponent;
        });
    }
}
