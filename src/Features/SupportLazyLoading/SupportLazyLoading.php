<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\ViewContext;
use function Livewire\{ on, store, trigger, wrap };
use Illuminate\Routing\Route;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Component;

class SupportLazyLoading extends ComponentHook
{
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

        Route::macro('defer', function ($enabled = true) {
            $this->defaults['defer'] = $enabled;

            return $this;
        });
    }

    public function mount($params)
    {
        $shouldBeLazy = false;
        $isDeferred = false;
        $isolate = true;

        if (isset($params['lazy']) && $params['lazy']) $shouldBeLazy = true;
        if (isset($params['lazy.bundle']) && $params['lazy.bundle']) $shouldBeLazy = true;
        if (isset($params['defer']) && $params['defer']) $shouldBeLazy = true;
        if (isset($params['defer.bundle']) && $params['defer.bundle']) $shouldBeLazy = true;

        if (isset($params['lazy']) && $params['lazy'] === 'on-load') $isDeferred = true;
        if (isset($params['lazy.bundle']) && $params['lazy.bundle'] === 'on-load') $isDeferred = true;
        if (isset($params['defer']) && $params['defer']) $isDeferred = true;
        if (isset($params['defer.bundle']) && $params['defer.bundle']) $isDeferred = true;

        if (isset($params['lazy.bundle']) && $params['lazy.bundle']) $isolate = false;
        if (isset($params['defer.bundle']) && $params['defer.bundle']) $isolate = false;

        $reflectionClass = new \ReflectionClass($this->component);
        $lazyAttribute = $reflectionClass->getAttributes(\Livewire\Attributes\Lazy::class)[0] ?? null;
        $deferAttribute = $reflectionClass->getAttributes(\Livewire\Attributes\Defer::class)[0] ?? null;

        // Apply attributes only if the corresponding param is not explicitly false...
        $lazyDisabled = isset($params['lazy']) && $params['lazy'] === false;
        $deferDisabled = isset($params['defer']) && $params['defer'] === false;

        if ($lazyAttribute && ! $lazyDisabled) $shouldBeLazy = true;
        if ($deferAttribute && ! $deferDisabled) $shouldBeLazy = true;
        if ($deferAttribute && ! $deferDisabled) $isDeferred = true;

        // If Livewire::withoutLazyLoading()...
        if (static::$disableWhileTesting) return;
        // If no lazy loading is included at all...
        if (! $shouldBeLazy) return;

        if ($lazyAttribute) {
            $attribute = $lazyAttribute->newInstance();

            if ($attribute->bundle !== null) $isolate = ! $attribute->bundle;
            if ($attribute->isolate !== null) $isolate = $attribute->isolate;
        }

        if ($deferAttribute) {
            $attribute = $deferAttribute->newInstance();

            if ($attribute->bundle !== null) $isolate = ! $attribute->bundle;
            if ($attribute->isolate !== null) $isolate = $attribute->isolate;
        }

        $this->component->skipMount();

        store($this->component)->set('isLazyLoadMounting', true);
        store($this->component)->set('isLazyIsolated', $isolate);

        $this->component->skipRender(
            $this->generatePlaceholderHtml($params, $isDeferred)
        );
    }

    public function hydrate($memo)
    {
        if (! isset($memo['lazyLoaded'])) return;
        if ($memo['lazyLoaded'] === true) return;

        $this->component->skipHydrate();

        store($this->component)->set('isLazyLoadHydrating', true);
    }

    function dehydrate($context)
    {
        if (store($this->component)->get('isLazyLoadMounting') === true) {
            $context->addMemo('lazyLoaded', false);
            $context->addMemo('lazyIsolated', store($this->component)->get('isLazyIsolated'));
        } elseif (store($this->component)->get('isLazyLoadHydrating') === true) {
            $context->addMemo('lazyLoaded', true);
        }
    }

    function call($method, $params, $returnEarly)
    {
        if ($method !== '__lazyLoad') return;

        [ $encoded ] = $params;

        $mountParams = $this->resurrectMountParams($encoded);

        $this->callMountLifecycleMethod($mountParams);

        $returnEarly();
    }

    public function generatePlaceholderHtml($params, $isDeferred = false)
    {
        $this->registerContainerComponent();

        $container = app('livewire')->new('__mountParamsContainer');

        $container->forMount = array_diff_key($params, array_flip(['lazy', 'defer']));

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
            ($isDeferred ? 'x-init' : 'x-intersect') => '$wire.__lazyLoad(\''.$encoded.'\')',
        ]);

        $replaceHtml = function ($newHtml) use (&$html) {
            $html = $newHtml;
        };

        $html = $finish($html, $replaceHtml, $viewContext);

        return $html;
    }

    protected function getPlaceholderView($component, $params)
    {
        // @todo: This is a hack. Fix this so it uses a deterministically generated name...
        $name = (string) str($this->component->getName())->afterLast('.');
        $compiledPlaceholder = "livewire-compiled::{$name}_placeholder";

        $globalPlaceholder = config('livewire.component_placeholder');

        if (view()->exists($compiledPlaceholder)) {
            $placeholderHtml = $compiledPlaceholder;
        } else if ($globalPlaceholder) {
            $placeholderHtml = view($globalPlaceholder)->render();
        } else {
            $placeholderHtml = '<div></div>';
        }

        $viewOrString = wrap($component)->withFallback($placeholderHtml)->placeholder($params);

        $properties = Utils::getPublicPropertiesDefinedOnSubclass($component);

        $view = Utils::generateBladeView($viewOrString, $properties);

        return $view;
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
