<?php

namespace Tests\Browser\Deployment;

use Livewire\Component as BaseComponent;
use Livewire\Features\SupportPostDeploymentInvalidation;

class Component extends BaseComponent
{
    public $randomProperty;

    public function hydrateRandomProperty()
    {
        // Property hydration hooks are called before 'component.hydrate.subsequent'
        // which is what the deployment invalidation feature listens for to do its
        // checks so we can change the deployment hash here to simulate a manual
        // change.
        SupportPostDeploymentInvalidation::$LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH = 'new';
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>
</div>
HTML;
    }
}
