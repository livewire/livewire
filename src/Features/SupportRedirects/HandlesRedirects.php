<?php

namespace Livewire\Features\SupportRedirects;

use function Livewire\store;

trait HandlesRedirects
{
    public function redirect($url, $navigate = null)
    {
        store($this)->set('redirect', $url);

        if ($navigate !== null) store($this)->set('redirectUsingNavigate', $navigate);

        $shouldSkipRender = ! config('livewire.render_on_redirect', false);

        $shouldSkipRender && $this->skipRender();
    }

    public function redirectRoute($name, $parameters = [], $absolute = true, $navigate = false)
    {
        $this->redirect(route($name, $parameters, $absolute), $navigate);
    }

    public function redirectAction($name, $parameters = [], $absolute = true, $navigate = false)
    {
        $this->redirect(action($name, $parameters, $absolute), $navigate);
    }
}
