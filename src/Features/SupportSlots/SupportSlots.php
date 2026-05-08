<?php

namespace Livewire\Features\SupportSlots;

use Livewire\ComponentHook;

use function Livewire\on;
use function Livewire\store;

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
        $slots = $memo['slots'] ?? [];

        if (empty($slots)) return;

        // If slot content was persisted (e.g. from a lazy component that skipped its
        // initial render), restore full Slots so {{ $slot }} renders the content...
        $hasContent = collect($slots)->contains(fn ($s) => isset($s['content']));

        if ($hasContent) {
            $this->component->withHydratedSlots($slots);
        } else {
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
            $entry = [
                'name' => $slot->getName(),
                'componentId' => $slot->getComponentId(),
                'parentId' => $slot->getParentId(),
            ];

            // If the slot has content and the render was skipped (e.g. lazy loading),
            // persist the content so it survives the dehydrate → hydrate cycle...
            if ($slot instanceof Slot && store($this->component)->get('skipRender', false)) {
                $entry['content'] = $slot->content;
            }

            $slotMemo[] = $entry;
        }

        if (! empty($slotMemo)) {
            $context->addMemo('slots', $slotMemo);
        }
    }
}
