<?php

namespace Livewire\Features\SupportSlots;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportSlots extends ComponentHook
{
    public static function provide()
    {
        on('mount.stub', function ($tag, $id, $params, $parent, $key, $slots) {
            // If a child component is skipped in a subsequent render, capture slots passed
            // into to it so that the parent can add them to the response for morphing...
            $parent->withChildSlots($slots, $id);
        });
    }

    public function render($view, $properties)
    {
        // Ensure that the slot proxy is available for child views...
        $slots = $this->component->getSlots();

        $slotProxy = new SlotProxy($this->component, $slots);

        $view->with([
            'slot' => $slotProxy,
            'slots' => $slotProxy,
        ]);
    }

    public function renderIsland($name, $view, $properties)
    {
        $slots = $this->component->getSlots();

        $slotProxy = new SlotProxy($this->component, $slots);

        $view->with([
            'slot' => $slotProxy,
            'slots' => $slotProxy,
        ]);
    }

    function hydrate($memo)
    {
        // When a child component re-renders, we will need to stub out the known slots
        // with placeholders so that they can be rendered and morph'd correctly...
        $slots = $memo['slots'] ?? [];

        if (! empty($slots)) {
            $this->component->withPlaceholderSlots($slots);
        }
    }

    public function dehydrate($context)
    {
        $this->dehydrateSlotsThatWereRenderedIntoMorphEffects($context);
        $this->dehydrateSlotsThatWerePassedToTheComponentForSubsequentRenders($context);
    }

    protected function dehydrateSlotsThatWereRenderedIntoMorphEffects($context)
    {
        // When a parent renders, capture the slots and include them in the response...
        $slots = $this->component->getSlotsForSkippedChildRenders();

        if (! empty($slots)) {
            $context->addEffect('slotFragments', $slots);
        }
    }

    protected function dehydrateSlotsThatWerePassedToTheComponentForSubsequentRenders($context)
    {
        // Ensure a child component is aware of what slots belong to it...
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
