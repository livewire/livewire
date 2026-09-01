<?php

namespace Livewire\Features\SupportHtmlAttributeForwarding;

use Illuminate\Contracts\Support\Htmlable;
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
            fn ($value) => ! is_array($value) && (! is_object($value) || $value instanceof Htmlable)
        );

        $view->with(['attributes' => new ComponentAttributeBag($attributes)]);
    }
}