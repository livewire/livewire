<?php

namespace Livewire\Features\SupportRenderless;

use Livewire\Features\SupportAttributes\AttributeLevel;
use Livewire\Features\SupportEvents\SupportEvents;

use function Livewire\store;

trait HandlesRenderless
{
    public function renderless()
    {
        $this->skipRender();
    }

    public function skipRender($html = null)
    {
        if (store($this)->has('forceRender')) {
            return;
        }

        store($this)->set('skipRender', $html ?: true);
    }

    public function skipIslandsRender()
    {
        store($this)->set('skipIslandsRender', true);
    }

    public function shouldSkipRender()
    {
        return store($this)->get('skipRender', false);
    }

    public function shouldSkipRenderAfterCalls($calls)
    {
        if (count($calls) === 0) return false;

        $renderlessMethods = $this->getAttributes()
            ->whereInstanceOf(BaseRenderless::class)
            ->filter(fn ($attribute) => $attribute->getLevel() === AttributeLevel::METHOD)
            ->map(fn ($attribute) => $attribute->getName());

        return collect($calls)->every(
            fn ($call) => ($call['metadata']['renderless'] ?? false)
                || $renderlessMethods->contains($this->resolveCalledMethod($call))
        );
    }

    public function shouldSkipIslandsRender()
    {
        return store($this)->get('skipIslandsRender', false);
    }

    protected function resolveCalledMethod($call)
    {
        if ($call['method'] !== '__dispatch' || ! isset($call['params'][0])) {
            return $call['method'];
        }

        $event = $call['params'][0];

        // Event listeners travel as __dispatch calls, but their attributes belong to the listener method...
        if (! in_array($event, SupportEvents::getListenerEventNames($this))) {
            return $call['method'];
        }

        return SupportEvents::getListenerMethodName($this, $event);
    }
}
