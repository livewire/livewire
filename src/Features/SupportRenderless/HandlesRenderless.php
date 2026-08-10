<?php

namespace Livewire\Features\SupportRenderless;

use Livewire\Features\SupportAttributes\AttributeLevel;

use function Livewire\store;

trait HandlesRenderless
{
    /** @deprecated Use skipRender() for an imperative render veto. */
    public function renderless()
    {
        $this->skipRender();
    }

    public function skipRender($html = null)
    {
        if (store($this)->has('forceRender')) {
            return;
        }

        $requestRendering = store($this)->get('requestRendering');

        if ($requestRendering?->preventAutomaticRenderingForTheEntireTargetScopeOfTheCurrentCall($html)) {
            return;
        }

        store($this)->set('skipRender', $html ?: true);
    }

    public function shouldSkipRender()
    {
        return store($this)->get('skipRender', false);
    }

    public function isRenderlessMethod($method)
    {
        return $this->getAttributes()
            ->whereInstanceOf(BaseRenderless::class)
            ->filter(fn ($attribute) => $attribute->getLevel() === AttributeLevel::METHOD)
            ->contains(fn ($attribute) => $attribute->getName() === $method);
    }
}
