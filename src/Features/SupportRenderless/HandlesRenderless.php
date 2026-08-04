<?php

namespace Livewire\Features\SupportRenderless;

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

    public function shouldSkipIslandsRender()
    {
        return store($this)->get('skipIslandsRender', false);
    }
}
