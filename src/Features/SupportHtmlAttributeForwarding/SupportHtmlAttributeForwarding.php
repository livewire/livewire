<?php

namespace Livewire\Features\SupportHtmlAttributeForwarding;

use Livewire\ComponentHook;
use Illuminate\View\ComponentAttributeBag;

class SupportHtmlAttributeForwarding extends ComponentHook
{
    public function render($view, $properties)
    {
        $attributes = $this->component->getHtmlAttributes();

        $view->with(['attributes' => new ComponentAttributeBag($attributes)]);
    }

    public function renderIsland($name, $view, $properties)
    {
        $attributes = $this->component->getHtmlAttributes();

        $view->with(['attributes' => new ComponentAttributeBag($attributes)]);
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
}