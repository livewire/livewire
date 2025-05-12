<?php

namespace Livewire\Features\SupportDeploymentInvalidation;

use Livewire\Component;

use Livewire\Exceptions\LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;
use Livewire\Livewire;

class DeploymentCanBeManuallyInvalidatedBrowserTest extends \Tests\BrowserTestCase
{
    public function test_if_the_page_is_manually_invalidated_it_should_show_the_page_expired_dialog()
    {
        Livewire::visit(new class extends Component {
            public function invalidateManually()
            {
                throw new LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="invalidateManually" dusk="invalidate">Invalidate</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewireToLoad()
            ->click('@invalidate')
            // Wait for Livewire to respond, but dusk helper won't
            // work as dialog box is stopping further execution
            ->pause(300)
            ->assertDialogOpened("This page has expired.\nWould you like to refresh the page?")
            // Dismiss dialog so next tests run
            ->dismissDialog()
        ;
    }
}
