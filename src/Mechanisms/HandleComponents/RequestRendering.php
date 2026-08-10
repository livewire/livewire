<?php

namespace Livewire\Mechanisms\HandleComponents;

use function Livewire\{invade, store};

class RequestRendering
{
    protected $automaticRenderingDecisionsByTarget = [];
    protected $callCurrentlyBeingHandled = null;
    protected $islandFragmentBatchesInServerOrder = [];
    protected $calls = [];

    public function __construct(protected $component) {}

    public function prepareToHandleTheCallBatch($calls)
    {
        $this->calls = $calls;

        $this->rememberAutomaticRenderingRequestedByTheCallBatch();
    }

    protected function rememberAutomaticRenderingRequestedByTheCallBatch()
    {
        if (count($this->calls) === 0) {
            $this->automaticRenderingDecisionFor(RenderTarget::root())
                ->rememberThatAutomaticRenderingWasRequested();

            return;
        }

        foreach ($this->calls as $call) {
            if ($call->renderless) continue;

            $decision = $this->automaticRenderingDecisionFor($call->target);

            $decision->rememberThatAutomaticRenderingWasRequested($call->mountIsland);

            if ($call->target->isIsland() && in_array($call->mode, ['append', 'prepend'])) {
                $decision->rememberThatCallOrderMustBePreserved();
            }
        }
    }

    public function callAtIndex($index)
    {
        return $this->calls[$index];
    }

    public function startHandlingCall(RenderCall $call)
    {
        $this->callCurrentlyBeingHandled = $call;
    }

    public function stopHandlingTheCurrentCall()
    {
        $this->callCurrentlyBeingHandled = null;
    }

    public function preventAutomaticRenderingForTheEntireTargetScopeOfTheCurrentCall($replacementHtml = null)
    {
        if (! $this->callCurrentlyBeingHandled) return false;

        $target = $this->callCurrentlyBeingHandled->target;

        $this->automaticRenderingDecisionFor($target)
            ->preventAutomaticRendering($replacementHtml);

        $this->forgetAutomaticIslandFragmentsAlreadyRenderedFor($target);

        return true;
    }

    public function renderAutomaticIslandOutputForTheCurrentCallNowIfItsModeRequiresPreservingCallOrder()
    {
        $call = $this->callCurrentlyBeingHandled;

        if (! $call) return;
        if ($call->renderless) return;
        if (! $call->target->isIsland()) return;

        $decision = $this->automaticRenderingDecisionFor($call->target);

        if (! $decision->callOrderMustBePreserved()) return;
        if (! $decision->automaticRenderingShouldHappen()) return;

        $fragments = $this->renderEveryIslandTokenNamed(
            $call->target->name,
            mode: $call->mode,
            mount: $call->mountIsland,
        );

        $this->rememberAutomaticIslandFragmentsThatMayStillBeVetoed(
            $call->target,
            $fragments,
        );
    }

    public function renderFinalStateForAutomaticIslandTargetsThatDidNotRequirePreservingCallOrder()
    {
        foreach ($this->automaticRenderingDecisionsByTarget as $decision) {
            if (! $decision->target->isIsland()) continue;
            if ($decision->callOrderMustBePreserved()) continue;
            if (! $decision->automaticRenderingShouldHappen()) continue;

            $fragments = $this->renderEveryIslandTokenNamed(
                $decision->target->name,
                mode: 'morph',
                mount: $decision->mountIsland,
            );

            $this->rememberAutomaticIslandFragmentsThatMayStillBeVetoed(
                $decision->target,
                $fragments,
            );
        }
    }

    public function renderExplicitIslandOutputRightNowAndRememberItCannotBeVetoed(
        $name,
        $content = null,
        $mode = 'morph',
        $with = [],
        $mount = false,
    ) {
        $target = RenderTarget::island($name);

        $fragments = $this->renderEveryIslandTokenNamed(
            $name,
            $content,
            $mode,
            $with,
            $mount,
        );

        $this->rememberIslandFragmentBatch(
            $target,
            $fragments,
            canBeDiscardedByAHardVeto: false,
        );
    }

    public function applyTheFinalRootRenderingDecisionToTheComponent()
    {
        $rootDecision = $this->automaticRenderingDecisionFor(RenderTarget::root());

        if ($rootDecision->automaticRenderingWasPrevented()) {
            store($this->component)->set(
                'skipRender',
                $rootDecision->replacementHtml ?: true,
            );

            return;
        }

        if (! $rootDecision->automaticRenderingShouldHappen()) {
            $this->component->skipRender($rootDecision->replacementHtml);
        }
    }

    public function islandFragmentsThatShouldBeSentToTheBrowser()
    {
        $fragments = [];

        foreach ($this->islandFragmentBatchesInServerOrder as $batch) {
            array_push($fragments, ...$batch['fragments']);
        }

        return $fragments;
    }

    protected function rememberAutomaticIslandFragmentsThatMayStillBeVetoed(
        RenderTarget $target,
        $fragments,
    ) {
        $this->rememberIslandFragmentBatch(
            $target,
            $fragments,
            canBeDiscardedByAHardVeto: true,
        );
    }

    protected function rememberIslandFragmentBatch(
        RenderTarget $target,
        $fragments,
        $canBeDiscardedByAHardVeto,
    ) {
        if (empty($fragments)) return;

        $this->islandFragmentBatchesInServerOrder[] = [
            'target' => $target,
            'fragments' => $fragments,
            'canBeDiscardedByAHardVeto' => $canBeDiscardedByAHardVeto,
        ];
    }

    protected function forgetAutomaticIslandFragmentsAlreadyRenderedFor(RenderTarget $target)
    {
        $this->islandFragmentBatchesInServerOrder = array_values(array_filter(
            $this->islandFragmentBatchesInServerOrder,
            fn ($batch) => ! (
                $batch['canBeDiscardedByAHardVeto']
                && $batch['target']->key() === $target->key()
            ),
        ));
    }

    protected function renderEveryIslandTokenNamed(
        $name,
        $content = null,
        $mode = 'morph',
        $with = [],
        $mount = false,
    ) {
        return invade($this->component)->renderFragmentsForEveryIslandTokenNamed(
            $name,
            $content,
            $mode,
            $with,
            $mount,
        );
    }

    protected function automaticRenderingDecisionFor(RenderTarget $target)
    {
        $key = $target->key();

        return $this->automaticRenderingDecisionsByTarget[$key]
            ??= new AutomaticRenderDecision($target);
    }
}
