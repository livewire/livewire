<?php

namespace Livewire\Mechanisms\HandleComponents;

class AutomaticRenderDecision
{
    protected $automaticRenderingWasRequested = false;
    protected $automaticRenderingWasPrevented = false;
    protected $callOrderMustBePreserved = false;
    public $mountIsland = false;
    public $replacementHtml = null;

    public function __construct(public RenderTarget $target) {}

    public function rememberThatAutomaticRenderingWasRequested($mountIsland = false)
    {
        $this->automaticRenderingWasRequested = true;
        $this->mountIsland = $this->mountIsland || $mountIsland;
    }

    public function preventAutomaticRendering($replacementHtml = null)
    {
        $this->automaticRenderingWasPrevented = true;

        if ($this->target->isRoot() && $replacementHtml !== null) {
            $this->replacementHtml = $replacementHtml;
        }
    }

    public function automaticRenderingShouldHappen()
    {
        return $this->automaticRenderingWasRequested
            && ! $this->automaticRenderingWasPrevented;
    }

    public function automaticRenderingWasPrevented()
    {
        return $this->automaticRenderingWasPrevented;
    }

    public function rememberThatCallOrderMustBePreserved()
    {
        $this->callOrderMustBePreserved = true;
    }

    public function callOrderMustBePreserved()
    {
        return $this->callOrderMustBePreserved;
    }
}
