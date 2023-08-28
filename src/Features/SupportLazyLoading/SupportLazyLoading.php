<?php

namespace Livewire\Features\SupportLazyLoading;

use function Livewire\{store, wrap};
use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use Livewire\Drawer\Utils;
use Livewire\ComponentHook;
use Livewire\Component;
use Illuminate\Routing\Route;

class SupportLazyLoading extends ComponentHook
{
    static $isLazyRoute = null;

    static function provide()
    {
        static::registerRouteMacro();
    }

    static function registerRouteMacro()
    {
        Route::macro('lazy', function ($enabled = true) {
            SupportLazyLoading::$isLazyRoute = $enabled;

            return $this;
        });
    }

    public function mount($params)
    {
        // Priority to full page component route macro if there
        if (SupportLazyLoading::$isLazyRoute !== null) {
            $lazyValue = SupportLazyLoading::$isLazyRoute ? 'on-load' : false;
        } else {
            $lazyValue = $params['lazy'] ?? null;
            // If lazy param is missing or null try to get lazy attribute
            if($lazyValue === null){
                $lazyAttribute = (new \ReflectionClass($this->component))->getAttributes(\Livewire\Attributes\Lazy::class)[0] ?? null;
                if ($lazyAttribute) {
                    // If there is the lazy attribute get the value or use true as default
                    $lazyValue = $lazyAttribute->getArguments()[0] ?? true;
                }
            }
        }

        if (!$lazyValue) return;

        $this->component->skipMount();

        store($this->component)->set('isLazyLoadMounting', true);

        $this->component->skipRender(
            $this->generatePlaceholderHtml($params, $lazyValue)
        );
    }

    public function hydrate($memo)
    {
        if (!isset($memo['lazyLoaded'])) return;
        if ($memo['lazyLoaded'] === true) return;

        $this->component->skipHydrate();

        store($this->component)->set('isLazyLoadHydrating', true);
    }

    function dehydrate($context)
    {
        if (store($this->component)->get('isLazyLoadMounting') === true) {
            $context->addMemo('lazyLoaded', false);
        } elseif (store($this->component)->get('isLazyLoadHydrating') === true) {
            $context->addMemo('lazyLoaded', true);
        }
    }

    function call($method, $params, $returnEarly)
    {
        if ($method !== '__lazyLoad') return;

        [$encoded] = $params;

        $mountParams = $this->resurrectMountParams($encoded);

        $this->callMountLifecycleMethod($mountParams);

        $returnEarly();
    }

    public function generatePlaceholderHtml($params, $lazyValue)
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
            ->placeholder();

        $html = Utils::insertAttributesIntoHtmlRoot($placeholder, [
            ($lazyValue === 'on-load' ? 'x-init' : 'x-intersect') => '$wire.__lazyLoad(\'' . $encoded . '\')',
        ]);

        return $html;
    }

    function resurrectMountParams($encoded)
    {
        $snapshot = json_decode(base64_decode($encoded), associative: true);

        $this->registerContainerComponent();

        [$container] = app('livewire')->fromSnapshot($snapshot);

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
