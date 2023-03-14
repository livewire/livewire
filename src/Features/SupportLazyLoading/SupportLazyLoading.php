<?php

namespace Livewire\Features\SupportLazyLoading;

use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use function Livewire\{ on, wrap };
use Livewire\Drawer\Utils;
use Livewire\ComponentHook;
use Livewire\Component;

class SupportLazyLoading extends ComponentHook
{
    public function mount($params)
    {
        if (! in_array('lazy', $params)) return;

        $this->component->skipMount();

        $mountParams = array_diff_key($params, array_flip(['lazy']));

        $this->component->skipRender(
            $this->generatePlaceholderHtml($mountParams)
        );
    }

    public function hydrate($memo)
    {
        if (isset($memo['lazyLoaded'])) return;

        $this->component->skipHydrate();
    }

    function dehydrate($context)
    {
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

        $snapshot = app('livewire')->snapshot(
            $container = app('livewire')->new('__mountParamsContainer', ['forMount' => $params])
        );

        $encoded = base64_encode(json_encode($snapshot));

        $placeholder = wrap($this->component)
            ->withFallback('<div></div>')
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
