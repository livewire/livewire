<?php

namespace Livewire\Macros;

class ViewMacros
{
    public function extends()
    {
        return function ($view, $params = []) {
            $this->livewireLayout = [
                'type' => 'extends',
                'slotOrSection' => 'content',
                'view' => $view,
                'params' => $params,
            ];

            return $this;
        };
    }

    public function layout()
    {
        return function ($view, $params = []) {
            $this->livewireLayout = [
                'type' => 'component',
                'slotOrSection' => 'slot',
                'view' => $view,
                'params' => $params,
            ];

            return $this;
        };
    }

    public function section()
    {
        return function ($section) {
            $this->livewireLayout['slotOrSection'] = $section;

            return $this;
        };
    }

    public function slot()
    {
        return function ($slot) {
            $this->livewireLayout['slotOrSection'] = $slot;

            return $this;
        };
    }
}
