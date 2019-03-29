<?php

namespace Livewire\Concerns;

trait MountsChildren
{
    protected $mountedChildren = [];

    protected function trackChildrenBeingMounted($renderCallback)
    {
        // The view gets rendered inside this callback.
        $dom = $renderCallback();

        // This allows us to recognize when a previosuly rendered child,
        // is no longer being rendered, we can clear their "children"
        // entry so that we don't still return dummy data.
        foreach ($this->children as $childName => $id) {
            if (! in_array($childName, $this->mountedChildren)) {
                unset($this->children[$childName]);
            }
        }

        return $dom;
    }

    public function mountChild($internalKey, $componentName, ...$options)
    {
        // I'm adding the component name to the "internalKey" to support dynamic components.
        $internalKey = $internalKey.$componentName;

        // If the child is new, we mount it. If not, we stub it out.
        $this->mountedChildren[] = $internalKey;

        if ($id = $this->children[$internalKey] ?? false) {
            return [
                // The "id" is included here as a key for morphdom.
                // @todo - if the root element of a component is not a "div", things will break,
                // because we are passing in a dummy div and morphdom will think it's a completely
                // different component.
                app('livewire')->injectComponentDataAsHtmlAttributesInRootElement('<div></div>', $id, 'not-serialized'),
                $id,
                'not-serialized',
            ];
        }

        [$dom, $id, $serialized] = app('livewire')->mount($componentName, ...$options);

        $this->children[$internalKey] = $id;

        return [$dom, $id, $serialized];
    }
}
