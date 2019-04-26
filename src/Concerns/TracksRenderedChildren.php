<?php

namespace Livewire\Concerns;

trait TracksRenderedChildren
{
    protected $renderedChildren = [];
    protected $previouslyRenderedChildren = [];

    public function getRenderedChildComponentId($id)
    {
        return $this->previouslyRenderedChildren[$id];
    }

    public function logRenderedChild($id, $componentId)
    {
        $this->renderedChildren[$id] = $componentId;
    }

    public function preserveRenderedChild($id)
    {
        $this->renderedChildren[$id] = $this->previouslyRenderedChildren[$id];
    }

    public function childHasBeenRendered($id)
    {
        return in_array($id, array_keys($this->previouslyRenderedChildren));
    }

    public function setPreviouslyRenderedChildren($children)
    {
        $this->previouslyRenderedChildren = $children;
    }

    public function getRenderedChildren()
    {
        return $this->renderedChildren;
    }
}
