<?php

namespace Livewire\Features\SupportSlots;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportSlots extends ComponentHook
{
    public static function provide()
    {
        on('mount.stub', function ($tag, $id, $params, $parent, $key, $slots) {
            $parent->withChildSlots($slots, $id);
        });
    }

    public function render($view, $properties)
    {
        $slots = $this->component->getSlots();

        $view->with(['slot' => new SlotProxy($this->component, $slots)]);
    }

    function hydrate($memo)
    {
        $slots = $memo['slots'] ?? [];

        if (! empty($slots)) {
            $this->component->withPlaceholderSlots($slots);
        }
    }

    public function dehydrate($context)
    {
        $this->dehydrateSlotsThatWerePassedToTheComponentForSubsequentRenders($context);

        $this->dehydrateSlotsThatWereRenderedIntoMorphEffects($context);
    }

    protected function dehydrateSlotsThatWereRenderedIntoMorphEffects($context)
    {
        $slots = $this->component->getSlotsForSkippedChildRenders();

        if (! empty($slots)) {
            $context->addEffect('slotFragments', $slots);
        }
    }

    protected function dehydrateSlotsThatWerePassedToTheComponentForSubsequentRenders($context)
    {
        $slots = $this->component->getSlots();

        $slotMemo = [];

        foreach ($slots as $slot) {
            $slotMemo[] = [
                'name' => $slot->getName(),
                'componentId' => $slot->getComponentId(),
                'parentId' => $slot->getParentId(),
            ];
        }

        if (! empty($slotMemo)) {
            $context->addMemo('slots', $slotMemo);
        }
    }
}
