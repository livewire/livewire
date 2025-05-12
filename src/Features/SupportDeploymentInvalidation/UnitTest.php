<?php

namespace Livewire\Features\SupportDeploymentInvalidation;

use Livewire\Component;
use Livewire\Exceptions\LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function test_deployment_invalidation_hash_is_added_to_the_snapshot()
    {
        $component = Livewire::test(new class extends Component {
            public function render()
            {
                return '<div></div>';
            }
        });

        $this->assertEquals(SupportDeploymentInvalidation::$LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH, $component->snapshot['memo']['invalid']);
    }

    public function test_deployment_invalidation_hash_is_checked_on_subsequent_requests()
    {
        $this->withoutExceptionHandling();
        $this->expectException(LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges::class);

        $component = Livewire::test(new class extends Component {
            public function render()
            {
                return '<div></div>';
            }
        });

        SupportDeploymentInvalidation::$LIVEWIRE_DEPLOYMENT_INVALIDATION_HASH = 'bob';

        $component->refresh();
    }
}
