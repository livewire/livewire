<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Livewire\Livewire;
use Tests\TestComponent;
use Tests\TestCase;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

class UnitTest extends TestCase
{
    public function test_it_injects_livewire_assets_before_closing_tags(): void
    {
        $this->compare(
            $livewireStyles = FrontendAssets::styles(),
            $livewireScripts = FrontendAssets::scripts(),
        <<<'HTML'
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
                <head>
                    <meta charset="utf-8"/>
                    <title></title>
                $livewireStyles</head>
                <body>
                $livewireScripts</body>
            </html>
        HTML);
    }

    public function test_it_injects_livewire_assets_html_only(): void
    {
        $this->compare(
            $livewireStyles = FrontendAssets::styles(),
            $livewireScripts = FrontendAssets::scripts(),
        <<<'HTML'
            <html>
                <yolo />
            </html>
        HTML, <<<HTML
            <html>$livewireStyles
                <yolo />
            $livewireScripts</html>
        HTML);
    }

    public function test_it_injects_livewire_assets_weirdly_formatted_html(): void
    {
        $this->compare(
            $livewireStyles = FrontendAssets::styles(),
            $livewireScripts = FrontendAssets::scripts(),
        <<<'HTML'
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
                >
                    <meta charset="utf-8"/>
                    <title></title>
                $livewireStyles</head>
                <body>
                $livewireScripts</body
                >
            </html>
        HTML);
    }

    public function test_it_injects_livewire_assets_html_with_header(): void
    {
        $this->compare(
            $livewireStyles = FrontendAssets::styles(),
            $livewireScripts = FrontendAssets::scripts(),
        <<<'HTML'
            <!doctype html>
            <HTML
                lang="en"
            >
                <Head
                >
                    <meta charset="utf-8"/>
                    <title></title>
                </Head>
                <bOdY>
                    <header class=""></header>
                </bOdY
                >
            </HTML>
        HTML, <<<HTML
            <!doctype html>
            <HTML
                lang="en"
            >
                <Head
                >
                    <meta charset="utf-8"/>
                    <title></title>
                $livewireStyles</Head>
                <bOdY>
                    <header class=""></header>
                $livewireScripts</bOdY
                >
            </HTML>
        HTML);
    }

    public function test_can_disable_auto_injection_using_global_method(): void
    {
        $this->markTestIncomplete();
    }

    public function test_can_disable_auto_injection_using_config(): void
    {
        config()->set('livewire.inject_assets', false);

        Route::get('/with-livewire', function () {
            return (new class Extends TestComponent {})();
        });

        Route::get('/without-livewire', function () {
            return Blade::render('<html></html>');
        });

        $this->get('/without-livewire')->assertDontSee('/livewire/livewire.min.js');
        $this->get('/with-livewire')->assertDontSee('/livewire/livewire.min.js');
    }

    public function test_can_force_injection_over_config(): void
    {
        config()->set('livewire.inject_assets', false);

        Route::get('/with-livewire', function () {
            return (new class Extends TestComponent {})();
        });

        Route::get('/without-livewire', function () {
            return '<html></html>';
        });

        \Livewire\Livewire::forceAssetInjection();
        $this->get('/with-livewire')->assertSee('/livewire/livewire.min.js');

        \Livewire\Livewire::flushState();
        \Livewire\Livewire::forceAssetInjection();
        $this->get('/without-livewire')->assertSee('/livewire/livewire.min.js');
    }

    public function test_only_auto_injects_when_a_livewire_component_was_rendered_on_the_page(): void
    {
        Route::get('/with-livewire', function () {
            return (new class Extends TestComponent {})();
        });

        Route::get('/without-livewire', function () {
            return '<html></html>';
        });

        $this->get('/without-livewire')->assertDontSee('/livewire/livewire.min.js');
        $this->get('/with-livewire')->assertSee('/livewire/livewire.min.js');
    }

    public function test_only_auto_injects_when_persist_was_rendered_on_the_page(): void
    {
        Route::get('/with-persist', function () {
            return Blade::render('<html>@persist("foo") ... @endpersist</html>');
        });

        Route::get('/without-persist', function () {
            return '<html></html>';
        });

        $this->get('/without-persist')->assertDontSee('/livewire/livewire.min.js');
        $this->get('/with-persist')->assertSee('/livewire/livewire.min.js');
    }

    public function test_only_injects_on_full_page_loads(): void
    {
        $this->markTestIncomplete();
    }

    public function test_only_inject_when_dev_doesnt_use_livewire_scripts_or_livewire_styles(): void
    {
        $this->markTestIncomplete();
    }

    public function test_response_maintains_original_view_after_asset_injection(): void
    {
        Livewire::component('foo', new class extends \Livewire\Component {
            public function render() {
                return '<div>Foo!</div>';
            }
        });

        $view = view('uses-component')->with('variable', 'cheese');

        Route::get('/with-livewire', fn() => $view);

        $response = $this->get('/with-livewire');

        $this->assertEquals($view, $response->original);

        $response
            ->assertSee('cheese')
            ->assertViewIs('uses-component')
            ->assertViewHas('variable', 'cheese');
    }

    protected function compare($forHead, $forBody, string $original, string $expected): void
    {
        $this->assertEquals($expected, SupportAutoInjectedAssets::injectAssets($original, $forHead, $forBody));
    }

    public function makeACleanSlate()
    {
        \Livewire\Livewire::flushState();

        parent::makeACleanSlate();
    }
}
