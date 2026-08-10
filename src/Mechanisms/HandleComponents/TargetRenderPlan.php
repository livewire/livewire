<?php

namespace Livewire\Mechanisms\HandleComponents;

class TargetRenderPlan
{
    protected $automaticRenderRequested = false;
    protected $automaticRenderVetoed = false;
    public $mountIsland = false;
    public $replacementHtml = null;

    public function __construct(
        public RenderTarget $target,
        protected $strategy = 'final-state',
    ) {}

    public function requestAutomaticRender($mountIsland = false)
    {
        $this->automaticRenderRequested = true;
        $this->mountIsland = $this->mountIsland || $mountIsland;
    }

    public function vetoAutomaticRender($replacementHtml = null)
    {
        $this->automaticRenderVetoed = true;

        if ($this->target->isRoot() && $replacementHtml !== null) {
            $this->replacementHtml = $replacementHtml;
        }
    }

    public function shouldRenderAutomatically()
    {
        return $this->automaticRenderRequested && ! $this->automaticRenderVetoed;
    }

    public function preserveCallOrder()
    {
        $this->strategy = 'ordered';
    }

    public function wasVetoed()
    {
        return $this->automaticRenderVetoed;
    }

    public function isOrdered()
    {
        return $this->strategy === 'ordered';
    }
}
