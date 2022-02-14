<?php

namespace Tests\Browser\Deployment;

use Livewire\Component as BaseComponent;
use Livewire\Exceptions\LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;

class ManualDeploymentComponent extends BaseComponent
{
    public $randomProperty;

    public function invalidateComponent()
    {
        throw new LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <button type="button" wire:click="invalidateComponent" dusk="invalidateComponent">Refresh</button>
</div>
HTML;
    }
}
