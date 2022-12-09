<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Tests\TestCase;

class Test extends TestCase
{
    public function setUp(): void
    {
        $this->markTestIncomplete();
    }
    
    /** @test */
    function can_disable_auto_injection_using_global_method() {}
    function can_disable_auto_injection_using_config() {}
    function only_auto_injects_when_a_livewire_component_was_rendered_on_the_page() {}
    function only_injects_on_full_page_loads() {}
    function only_inject_when_dev_doesnt_use_livewire_scripts_or_livewire_styles() {}
}
