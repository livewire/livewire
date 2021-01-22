<?php

namespace Livewire;

use Illuminate\Routing\Redirector as BaseRedirector;

class Redirector extends BaseRedirector
{
    public function to($path, $status = 302, $headers = [], $secure = null)
    {
        $this->component->redirect($this->generator->to($path, [], $secure));

        return $this;
    }

    public function away($path, $status = 302, $headers = [])
    {
        return $this->to($path, $status, $headers);
    }

    public function component(Component $component)
    {
        $this->component = $component;

        return $this;
    }

    public function response($to)
    {
        return $this->createRedirect($to, 302, []);
    }
}
