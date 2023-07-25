<?php

namespace Livewire\Features\SupportRedirects;

use function Livewire\store;

trait HandlesRedirects
{
    public function redirect($url, $navigate = false)
    {
        store($this)->set('redirect', $url);

        if ($navigate) store($this)->set('redirectUsingNavigate', true);

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
