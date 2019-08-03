<?php

namespace Livewire\Routing;

use Livewire\Component;
use Illuminate\Routing\Redirector as IlluminateRedirector;

class Redirector extends IlluminateRedirector
{
    /**
     * Set the redirect for Livewire to the given path.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @param  bool|null    $secure
     * @return self
     */
    public function to($path, $status = 302, $headers = [], $secure = null)
    {
        $this->component->redirect($this->generator->to($path, [], $secure));

        return $this;
    }

    /**
     * The Component resolving the Redirector.
     *
     * @param \Livewire\Component $component
     * @return void
     */
    public function component(Component $component)
    {
        $this->component = $component;

        return $this;
    }
}
