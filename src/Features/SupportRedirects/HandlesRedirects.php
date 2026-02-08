<?php

namespace Livewire\Features\SupportRedirects;

use function Livewire\store;

trait HandlesRedirects
{
    public function redirect($url, $navigate = false, $replace = false)
    {
        store($this)->set('redirect', $url);

        if ($navigate) store($this)->set('redirectUsingNavigate', true);

        if ($replace) store($this)->set('redirectReplace', true);

        $shouldSkipRender = ! config('livewire.render_on_redirect', false);

        $shouldSkipRender && $this->skipRender();
    }

    public function redirectRoute($name, $parameters = [], $absolute = true, $navigate = false, $replace = false)
    {
        $this->redirect(route($name, $parameters, $absolute), $navigate, $replace);
    }

    public function redirectIntended($default = '/', $navigate = false, $replace = false)
    {
        $url = session()->pull('url.intended', $default);

        $this->redirect($url, $navigate, $replace);
    }

    public function redirectAction($name, $parameters = [], $absolute = true, $navigate = false, $replace = false)
    {
        $this->redirect(action($name, $parameters, $absolute), $navigate, $replace);
    }
}
