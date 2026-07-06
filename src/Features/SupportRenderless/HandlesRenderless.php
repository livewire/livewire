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
}