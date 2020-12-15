<?php

namespace Livewire\ComponentConcerns;

trait PerformsRedirects
{
    public $redirectTo;

    public function redirect($url)
    {
        $this->redirectTo = $url;
        $this->shouldSkipRender = true;
    }

    public function redirectRoute($name, $parameters = [], $absolute = true)
    {
        $this->redirectTo = route($name, $parameters, $absolute);
        $this->shouldSkipRender = true;
    }

    public function redirectAction($name, $parameters = [], $absolute = true)
    {
        $this->redirectTo = action($name, $parameters, $absolute);
        $this->shouldSkipRender = true;
    }
}
