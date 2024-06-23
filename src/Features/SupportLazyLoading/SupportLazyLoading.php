<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
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

        store($this->component)->set('isLazyLoadMounting', true);
        store($this->component)->set('isLazyIsolated', $isolate);

        $this->component->skipRender(
            $this->generatePlaceholderHtml($params)
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

    public function generatePlaceholderHtml($params)
    {
        $this->registerContainerComponent();

        $container = app('livewire')->new('__mountParamsContainer');

        $container->forMount = array_diff_key($params, array_flip(['lazy']));

        $snapshot = app('livewire')->snapshot($container);

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
