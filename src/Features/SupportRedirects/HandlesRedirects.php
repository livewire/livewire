<?php

namespace Livewire\Features\SupportRedirects;

use Livewire\Mechanisms\ComponentDataStore;

trait HandlesRedirects
{
    public function redirect($url)
    {
        ComponentDataStore::set($this, 'redirect', $url);

        $shouldSkipRender = ! config('livewire.render_on_redirect', false);

        $shouldSkipRender && $this->skipRender();
    }

    public function redirectRoute($name, $parameters = [], $absolute = true)
    {
        $to = route($name, $parameters, $absolute);

        ComponentDataStore::set($this, 'redirect', $to);

        $shouldSkipRender = ! config('livewire.render_on_redirect', false);

        $shouldSkipRender && $this->skipRender();
    }

    public function redirectAction($name, $parameters = [], $absolute = true)
    {
        $to = action($name, $parameters, $absolute);

        ComponentDataStore::set($this, 'redirect', $to);

        $shouldSkipRender = ! config('livewire.render_on_redirect', false);

        $shouldSkipRender && $this->skipRender();
    }
}
