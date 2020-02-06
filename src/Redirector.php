<?php

namespace Livewire;

use Livewire\Component;
use Illuminate\Routing\Redirector as BaseRedirector;

class Redirector extends BaseRedirector
{
    public function to($path, $status = 302, $headers = [], $secure = null)
    {
        $this->component->redirect($this->generator->to($path, [], $secure));

        return $this;
    }

    public function component(Component $component)
    {
        $this->component = $component;

        return $this;
    }
}
