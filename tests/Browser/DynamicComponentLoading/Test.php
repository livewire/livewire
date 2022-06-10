<?php

namespace Tests\Browser\DynamicComponentLoading;

use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test_that_component_loaded_dynamically_via_post_action_causes_no_method_not_allowed()
    {
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

    public function test_that_components_in_shadow_dom_can_have_wire_directives()
    {
        File::makeDirectory($this->livewireClassesPath('App'), 0755, true);

        $this->browse(function (Browser $browser) {
            $browser->visit(route('shadow-dom-component', [], false))
                ->waitForText('Step 1 Active')
                ->script('window.shadowButton.click()');
            $browser->waitForText('Test succeeded')
                    ->assertSee('Test succeeded');
        });
    }
}
