<?php

namespace LegacyTests\Browser\DynamicComponentLoading;

use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test_that_component_loaded_dynamically_via_post_action_causes_no_method_not_allowed()
    {
        $this->markTestSkipped(); // @todo: Josh Hanley

        File::makeDirectory($this->livewireClassesPath('App'), 0755, true);

        $this->browse(function (Browser $browser) {
            $browser->visit(route('load-dynamic-component', [], false))
                ->waitForText('Step 1 Active')
                ->waitFor('#click_me')
                ->click('#click_me')
                ->waitForText('Test succeeded')
                ->assertSee('Test succeeded');
        });
    }
}
