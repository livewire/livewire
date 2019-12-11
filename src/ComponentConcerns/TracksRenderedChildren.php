<?php

namespace Livewire\ComponentConcerns;

trait TracksRenderedChildren
{
    protected $renderedChildren = [];
    protected $previouslyRenderedChildren = [];

    public function getRenderedChildComponentId($id)
    {
        return $this->previouslyRenderedChildren[$id]['id'];
    }

    public function getRenderedChildComponentTagName($id)
    {
        return $this->previouslyRenderedChildren[$id]['tag'];
    }

    public function logRenderedChild($id, $componentId, $tagName)
    {
        $this->renderedChildren[$id] = ['id' => $componentId, 'tag' => $tagName];
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
