<?php

namespace Livewire\Concerns;

trait TracksChildren
{
    protected $mountedChildren = [];

    protected function trackChildrenBeingMounted($renderCallback)
    {
        // The view gets rendered in this callback, therefore rendering
        // all children declared inside the biew.
        $dom = $renderCallback();

        // This allows us to recognize when a previosuly rendered child,
        // is no longer being rendered, we can clear their "children"
        // entry so that we don't still return dummy data.
        foreach ($this->wrapped->children as $childName => $id) {
            if (! in_array($childName, $this->mountedChildren)) {
                unset($this->wrapped->children[$childName]);
            }
        }

        return $dom;
    }

    public function mountChild($componentName, ...$options)
    {
        $this->mountedChildren[] = $componentName;

        // Note: this only allows for one child component of each type in a component.
        if ($id = $this->wrapped->children[$componentName] ?? false) {
            return [
                // The "id" is included here as a key for morphdom.
                // @todo - if the root element of a component is not a "div", things will break,
                // because we are passing in a dummy div and morphdom will whink it's a completely
                // different component.
                app('livewire')->injectDataForJsInComponentRootAttributes('<div></div>', $id, 'not-serialized'),
                $id,
                'not-serialized',
            ];
        }

        [$dom, $id, $serialized] = app('livewire')->mount($componentName, ...$options);

        $this->wrapped->children[$componentName] = $id;

        return [$dom, $id, $serialized];
    }
}
