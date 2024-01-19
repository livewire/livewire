<?php

namespace Livewire\Mechanisms\FrontendAssets;

use function Livewire\trigger;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function styles()
    {
        $assets = app(FrontendAssets::class);

        $this->assertFalse($assets->hasRenderedStyles);

        $this->assertStringStartsWith('<!-- Livewire Styles -->', $assets->styles());

        $this->assertStringNotContainsString('data-livewire-style', $assets->styles());

        $this->assertStringContainsString('nonce="test" data-livewire-style', $assets->styles(['nonce' => 'test']));

        $this->assertTrue($assets->hasRenderedStyles);
    }

    /** @test */
    public function scripts()
    {
        $assets = app(FrontendAssets::class);

        $this->assertFalse($assets->hasRenderedScripts);

        $this->assertStringStartsWith('<script src="', $assets->scripts());

        $this->assertTrue($assets->hasRenderedScripts);
    }

    /** @test */
    public function flush_state_event_resets_has_rendered()
    {
        $assets = app(FrontendAssets::class);

        $assets->styles();
        $assets->scripts();

        $this->assertTrue($assets->hasRenderedStyles);
        $this->assertTrue($assets->hasRenderedScripts);

        trigger('flush-state');

        $this->assertFalse($assets->hasRenderedScripts);
        $this->assertFalse($assets->hasRenderedStyles);
    }

    /** @test */
    public function js_does_not_prepend_slash_for_url()
    {
        $url = 'https://example.com/livewire/livewire.js';
        $this->assertStringStartsWith('<script src="'.$url, FrontendAssets::js(['url' => $url]));
    }

    public function js_prepends_slash_for_non_url()
    {
        $url = 'livewire/livewire.js';
        $this->assertStringStartsWith('<script src="/'.$url, FrontendAssets::js(['url' => $url]));
    }
}
