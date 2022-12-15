<?php

namespace Livewire;

use Illuminate\Routing\Redirector as BaseRedirector;

#[\AllowDynamicProperties]
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

    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $this->session->flash($k, $v);
        }

        return $this;
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
