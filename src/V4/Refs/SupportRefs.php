<?php

namespace Livewire\V4\Refs;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

class SupportRefs extends ComponentHook
{
    public function mount($params)
    {
        if (isset($params['wire:ref'])) {
            $this->component->withRef($params['wire:ref']);
        }
    }

    public function dehydrate($context)
    {
        if ($this->component->hasRef()) {
            $context->addMemo('ref', $this->component->getRef());
        }
    }

    public function hydrate($memo)
    {
        $ref = $memo['ref'] ?? null;

        if (! $ref) return;

        $this->component->withRef($ref);
    }

    public function render($view, $data)
    {
        return function ($html, $replaceHtml) {
            $ref = $this->component->getRef();

            if (! $ref) return;

            $replaceHtml(Utils::insertAttributesIntoHtmlRoot($html, [
                'wire:ref' => $ref,
            ]));
        };
    }
}
