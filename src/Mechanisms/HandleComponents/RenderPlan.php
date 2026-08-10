<?php

namespace Livewire\Mechanisms\HandleComponents;

class RenderPlan
{
    protected $targets = [];
    protected $activeCall = null;
    protected $fragmentCommands = [];
    protected $nextSequence = 0;

    public function __construct(protected $calls)
    {
        $this->prepareTargets();
    }

    protected function prepareTargets()
    {
        if (count($this->calls) === 0) {
            $this->target(RenderTarget::root())->requestAutomaticRender();

            return;
        }

        foreach ($this->calls as $call) {
            $target = $this->target($call->target);

            if ($call->renderless) continue;

            $target->requestAutomaticRender($call->mountIsland);

            if ($call->target->isIsland() && in_array($call->mode, ['append', 'prepend'])) {
                $target->preserveCallOrder();
            }
        }
    }

    public function activate(RenderCall $call)
    {
        $this->activeCall = $call;
    }

    public function deactivate()
    {
        $this->activeCall = null;
    }

    public function vetoActiveTarget($replacementHtml = null)
    {
        if (! $this->activeCall) return false;

        $this->target($this->activeCall->target)->vetoAutomaticRender($replacementHtml);

        return true;
    }

    public function completeActiveCall($renderIsland)
    {
        if (! $this->activeCall) return;
        if ($this->activeCall->renderless) return;
        if (! $this->activeCall->target->isIsland()) return;

        $target = $this->target($this->activeCall->target);

        if (! $target->isOrdered()) return;
        if ($target->wasVetoed()) return;

        $fragments = $renderIsland(
            $this->activeCall->target->name,
            $this->activeCall->mode,
            $this->activeCall->mountIsland,
        );

        $this->recordFragments($this->activeCall->target, 'automatic', $fragments);
    }

    public function recordExplicitIslandFragments($name, $fragments)
    {
        $this->recordFragments(RenderTarget::island($name), 'explicit', $fragments);
    }

    public function finalize($renderIsland)
    {
        foreach ($this->targets as $target) {
            if (! $target->target->isIsland()) continue;
            if ($target->isOrdered()) continue;
            if (! $target->shouldRenderAutomatically()) continue;

            $fragments = $renderIsland(
                $target->target->name,
                'morph',
                $target->mountIsland,
            );

            $this->recordFragments($target->target, 'automatic', $fragments);
        }
    }

    public function shouldRenderRoot()
    {
        return $this->target(RenderTarget::root())->shouldRenderAutomatically();
    }

    public function rootRenderWasVetoed()
    {
        return $this->target(RenderTarget::root())->wasVetoed();
    }

    public function rootReplacementHtml()
    {
        return $this->target(RenderTarget::root())->replacementHtml;
    }

    public function islandFragments()
    {
        $fragments = [];
        $commands = $this->fragmentCommands;

        usort($commands, fn ($left, $right) => $left['sequence'] <=> $right['sequence']);

        foreach ($commands as $command) {
            $target = $this->target($command['target']);

            if ($command['source'] === 'automatic' && $target->wasVetoed()) continue;

            array_push($fragments, ...$command['fragments']);
        }

        return $fragments;
    }

    protected function recordFragments(RenderTarget $target, $source, $fragments)
    {
        if (empty($fragments)) return;

        $this->fragmentCommands[] = [
            'sequence' => $this->nextSequence++,
            'target' => $target,
            'source' => $source,
            'fragments' => $fragments,
        ];
    }

    protected function target(RenderTarget $target)
    {
        $key = $target->key();

        return $this->targets[$key] ??= new TargetRenderPlan($target);
    }
}
