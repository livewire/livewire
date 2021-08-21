<?php

namespace Livewire\ComponentConcerns;

trait PerformsRedirects
{
    public $redirectTo;

    public function redirect($url)
    {
        $this->redirectTo = $url;
        // This is set to true if no config found, as this was the default for Livewire
        $this->shouldSkipRender = $this->shouldSkipRender ?? config('livewire.should_skip_render_on_redirect', true);
    }

    public function redirectRoute($name, $parameters = [], $absolute = true)
    {
        $this->redirectTo = route($name, $parameters, $absolute);
        // This is set to true if no config found, as this was the default for Livewire
        $this->shouldSkipRender = $this->shouldSkipRender ?? config('livewire.should_skip_render_on_redirect', true);
    }

    public function redirectAction($name, $parameters = [], $absolute = true)
    {
        $this->redirectTo = action($name, $parameters, $absolute);
        // This is set to true if no config found, as this was the default for Livewire
        $this->shouldSkipRender = $this->shouldSkipRender ?? config('livewire.should_skip_render_on_redirect', true);
    }
}
