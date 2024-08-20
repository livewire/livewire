<?php

namespace Livewire\Features\SupportPageComponents;

use Illuminate\View\AnonymousComponent;
use Livewire\Mechanisms\HandleComponents\ViewContext;

class PageComponentConfig
{
    public $slots = [];
    public $viewContext = null;
    public $response;

    function __construct(
        public $type = '',
        public $view = '',
        public $slotOrSection = '',
        public $params = [],
    ) {
        $this->type = $view ?: config('livewire.layout-type', 'component');
        $this->view = $view ?: config('livewire.layout');
        $this->$slotOrSection = $view ?: config('livewire.layout-slot-or-section', 'slot');
        $this->viewContext = new ViewContext;
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
            $params = array_merge($params, $layout->resolveView()->getData());
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
