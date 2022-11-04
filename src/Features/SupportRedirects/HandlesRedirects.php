<?php

namespace Livewire\Features\SupportRedirects;

use Livewire\Mechanisms\DataStore;

use function Livewire\store;

trait HandlesRedirects
{
    public function redirect($url)
    {
        store($this)->set('redirect', $url);

        $shouldSkipRender = ! config('livewire.render_on_redirect', false);

        $shouldSkipRender && $this->skipRender();
    }

    public function redirectRoute($name, $parameters = [], $absolute = true)
    {
        $to = route($name, $parameters, $absolute);

        store($this)->set('redirect', $to);

        $shouldSkipRender = ! config('livewire.render_on_redirect', false);

        $shouldSkipRender && $this->skipRender();
    }

    public function redirectAction($name, $parameters = [], $absolute = true)
    {
        $to = action($name, $parameters, $absolute);

        store($this)->set('redirect', $to);

        $shouldSkipRender = ! config('livewire.render_on_redirect', false);

        $shouldSkipRender && $this->skipRender();
    }
}
