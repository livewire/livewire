<?php

namespace Livewire\Features\SupportHtmlAttributeForwarding;

use Livewire\ComponentHook;
use Illuminate\View\ComponentAttributeBag;

class SupportHtmlAttributeForwarding extends ComponentHook
{
    public function render($view, $properties)
    {
        $this->forwardAttributesToView($view);
    }

    public function renderIsland($name, $view, $properties)
    {
        $this->forwardAttributesToView($view);
    }

    public function renderPlaceholder($view, $properties)
    {
        $this->forwardAttributesToView($view);
    }

    function hydrate($memo)
    {
        $attributes = $memo['attributes'] ?? [];

        if (! empty($attributes)) {
            $this->component->withHtmlAttributes($attributes);
        }
    }

    public function dehydrate($context)
    {
        $attributes = $this->component->getHtmlAttributes();

        if (! empty($attributes)) {
            $context->addMemo('attributes', $attributes);
        }
    }

    protected function forwardAttributesToView($view)
    {
        $attributes = array_filter(
            $this->component->getHtmlAttributes(),
            fn ($attribute) => ! is_array($attribute) && ! is_object($attribute)
        );

        $view->with(['attributes' => new ComponentAttributeBag($attributes)]);
    }
}