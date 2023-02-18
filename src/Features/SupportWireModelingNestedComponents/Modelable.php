<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use function Livewire\store;

use Livewire\PropertyHook;
use Livewire\Drawer\Utils;

class Modelable extends PropertyHook
{
    public $outer;

    public function childMount($parent, $params)
    {

    }

    public function mount($params, $parent)
    {
        if ($parent && isset($params['wire:model'])) {
            $this->outer = $params['wire:model'];

            // $inner = $this->getName();

            // store($this->component)->push('wireModels', $inner, $outer);

            $this->setValue($parent->$this->outer);
        }
    }

    public function hydrate($meta, $parent, $params)
    {
        // If we're only re-rendering this component, there's no parent to pull from...

        // If
        $this->setValue();
    }

    public function dehydrate($context)
    {
        return function () use ($context) {
            $context->effects['html'] = Utils::insertAttributesIntoHtmlRoot($context->effects['html'], [
                'x-model' => '$wire.$parent.'.$this->outer,
                'x-modelable' => '$wire.'.$this->getName(),
            ]);
        };
    }
}
