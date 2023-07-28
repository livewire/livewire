<?php

namespace Livewire\Features\SupportPageComponents;

use Illuminate\View\AnonymousComponent;

class LayoutConfig
{
    public $slots = [];

    function __construct(
        public $type = 'component',
        public $view = '',
        public $slotOrSection = 'slot',
        public $params = [],
    ) {
        $this->view = $view ?: config('livewire.layout');
    }

    function mergeParams($toMerge)
    {
        $this->params = array_merge($toMerge, $this->params);
    }

    function normalizeViewNameAndParamsForBladeComponents()
    {
        // If a user passes the class name of a Blade component to the
        // layout macro (or uses inside their config), we need to
        // convert it to it's "view" name so Blade doesn't break.
        $view = $this->view;
        $params = $this->params;

        $attributes = $params['attributes'] ?? [];
        unset($params['attributes']);

        if (is_subclass_of($view, \Illuminate\View\Component::class)) {
            $layout = app()->makeWith($view, $params);
            $view = $layout->resolveView()->name();
        } else {
            $layout = new AnonymousComponent($view, $params);
        }

        $layout->withAttributes($attributes);

        $params = array_merge($params, $layout->data());

        $this->view = $view;
        $this->params = $params;

        // Remove default slot if present...
        if (isset($this->slots['default'])) unset($this->slots['default']);

        return $this;
    }
}
