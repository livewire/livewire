<?php

namespace Livewire\Features\SupportWireRef;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

class SupportWireRef extends ComponentHook
{
    public function mount($params)
    {
        if (isset($params['wire:ref'])) {
            $this->storeSet('ref', $params['wire:ref']);
        }
    }

    public function dehydrate($context)
    {
        if ($this->storeHas('ref')) {
            $context->addMemo('ref', $this->storeGet('ref'));
        }
    }

    public function hydrate($memo)
    {
        $ref = $memo['ref'] ?? null;

        if (! $ref) return;

        $this->storeSet('ref', $ref);
    }

    public function render($view, $data)
    {
        return function ($html, $replaceHtml) {
            $ref = $this->storeGet('ref');

            if (! $ref) return;

            $replaceHtml(Utils::insertAttributesIntoHtmlRoot($html, [
                'wire:ref' => $ref,
            ]));
        };
    }
}
