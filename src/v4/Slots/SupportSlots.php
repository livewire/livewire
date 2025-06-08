<?php

namespace Livewire\V4\Slots;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

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

        $view->with(['slot' => new SlotProxy($slots)]);
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
            $context->addEffect('slots', $slots);
        }
    }

    protected function dehydrateSlotsThatWerePassedToTheComponentForSubsequentRenders($context)
    {
        $slots = $this->component->getSlots();

        $slotMemo = [];

        foreach ($slots as $name => $slot) {
            $slotMemo[$name] = [
                'name' => $slot->getName(),
                'parentId' => $slot->getParentId(),
            ];
        }

        if (! empty($slotMemo)) {
            $context->addMemo('slots', $slotMemo);
        }
    }
}
