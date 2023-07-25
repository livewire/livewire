<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Tests\TestCase;

class UnitTest extends TestCase
{
    /** @test */
    public function it_injects_livewire_assets_before_closing_tags(): void
    {
        $livewireStyles = FrontendAssets::styles();
        $livewireScripts = FrontendAssets::scripts();

        $this->compare(<<<'HTML'
            <!doctype html>
            <html>
                <head>
                    <meta charset="utf-8"/>
                    <title></title>
                </head>
                <body>
                </body>
            </html>
        HTML, <<<HTML
            <!doctype html>
            <html>
                <head>$livewireStyles
                    <meta charset="utf-8"/>
                    <title></title>
                </head>
                <body>
                $livewireScripts</body>
            </html>
        HTML);
    }

    /** @test */
    public function it_injects_livewire_assets_html_only(): void
    {
        $livewireStyles = FrontendAssets::styles();
        $livewireScripts = FrontendAssets::scripts();

        $this->compare(<<<'HTML'
            <html>
                <yolo />
            </html>
        HTML, <<<HTML
            <html>$livewireStyles
                <yolo />
            $livewireScripts</html>
        HTML);
    }

    /** @test */
    public function it_injects_livewire_assets_weirdly_formatted_html(): void
    {
        $livewireStyles = FrontendAssets::styles();
        $livewireScripts = FrontendAssets::scripts();

        $this->compare(<<<'HTML'
            <!doctype html>
            <html
                lang="en"
            >
                <head
                >
                    <meta charset="utf-8"/>
                    <title></title>
                </head>
                <body>
                </body
                >
            </html>
        HTML, <<<HTML
            <!doctype html>
            <html
                lang="en"
            >
                <head
                >$livewireStyles
                    <meta charset="utf-8"/>
                    <title></title>
                </head>
                <body>
                $livewireScripts</body
                >
            </html>
        HTML);
    }

    /** @test */
    public function can_disable_auto_injection_using_global_method(): void
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function can_disable_auto_injection_using_config(): void
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function only_auto_injects_when_a_livewire_component_was_rendered_on_the_page(): void
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function only_injects_on_full_page_loads(): void
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function only_inject_when_dev_doesnt_use_livewire_scripts_or_livewire_styles(): void
    {
        $this->markTestIncomplete();
    }

    protected function compare(string $original, string $expected): void
    {
        $this->assertEquals($expected, SupportAutoInjectedAssets::injectAssets($original));
    }
}
