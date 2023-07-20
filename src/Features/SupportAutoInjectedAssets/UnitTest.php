<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UnitTest extends TestCase
{
    /** @test */
    public function it_injects_livewire_assets_before_closing_tags()
    {
        $manipulatedHtml = SupportAutoInjectedAssets::injectAssets(<<<'HTML'
            <!doctype html>
            <html>
                <head>
                    <meta charset="utf-8"/>
                    <title></title>
                </head>
                <body>
                </body>
            </html>
        HTML);

        $livewireStyles = Blade::render('@livewireStyles');
        $livewireScripts = Blade::render('@livewireScripts');

        $this->assertEquals(<<<HTML
            <!doctype html>
            <html>
                <head>
                    <meta charset="utf-8"/>
                    <title></title>
                $livewireStyles</head>
                <body>
                $livewireScripts</body>
            </html>
        HTML, $manipulatedHtml);
    }

    /** @test */
    public function can_disable_auto_injection_using_global_method()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function can_disable_auto_injection_using_config()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function only_auto_injects_when_a_livewire_component_was_rendered_on_the_page()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function only_injects_on_full_page_loads()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function only_inject_when_dev_doesnt_use_livewire_scripts_or_livewire_styles()
    {
        $this->markTestIncomplete();
    }
}
