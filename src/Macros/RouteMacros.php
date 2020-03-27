<?php

namespace Livewire\Macros;

class RouteMacros
{
    public function layout()
    {
        return function ($layout, $params = []) {
            if (isset($this->action['layout'])) {
                // If ->layout() has already been called in a parent root,
                // we want to nest the new layouts rather than overriding them.
                $this->action['layout'] = $this->action['layout'].$layout;
            } else {
                $this->action['layout'] = $layout;
            }

            $this->layoutParamsFromLivewire = $params;

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
