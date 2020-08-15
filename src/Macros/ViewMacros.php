<?php

namespace Livewire\Macros;

class ViewMacros
{
    public function extends()
    {
        return function ($view, $params = []) {
            $this->livewireLayout = [
                'type' => 'extends',
                'sectionOrSlot' => 'content',
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
                'sectionOrSlot' => 'default',
                'view' => $view,
                'params' => $params,
            ];

            return $this;
        };
    }

    public function section()
    {
        return function ($section) {
            $this->livewireLayout['sectionOrSlot'] = $section;

            return $this;
        };
    }

    public function slot()
    {
        return function ($slot) {
            $this->livewireLayout['sectionOrSlot'] = $slot;

            return $this;
        };
    }
}
