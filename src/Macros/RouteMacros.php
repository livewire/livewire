<?php

namespace Livewire\Macros;

class RouteMacros
{
    public function layout()
    {
        return function ($layout) {
            $this->action['layout'] = isset($this->action['layout'])
                ? $this->action['layout'].$layout
                : $layout;

            return $this;
        };
    }

    public function section()
    {
        return function ($section) {
            $this->action['section'] = $section;

            return $this;
        };
    }
}
