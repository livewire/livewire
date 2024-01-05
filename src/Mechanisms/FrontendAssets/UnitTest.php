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
}
