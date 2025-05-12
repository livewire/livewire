<?php

namespace Livewire\Features\SupportMorphAwareBladeCompilation;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys;
use Livewire\Livewire;

// This test needs to be in its own file, because if we try to disable the feature 
// in the same test as the feature enabled test the test will fail because the 
// precompiler will have already been registered....
class MorphLoopMarkersDisabledUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Livewire::flushState();

        config()->set('livewire.inject_morph_markers', true);
        config()->set('livewire.compiled_wire_keys', false);

        // We need to call these so provide gets called again to load the new config...
        ComponentHookRegistry::register(SupportMorphAwareBladeCompilation::class);
        ComponentHookRegistry::register(SupportCompiledWireKeys::class);
    }

    public function test_loop_markers_are_not_output_when_compiled_wire_keys_are_disabled()
    {
        $compiled = $this->compile(<<< 'HTML'
        <div>
            @foreach([1, 2, 3] as $item)
                <div wire:key="{{ $item }}">
                    {{ $item }}
                </div>
            @endforeach
        </div>
        HTML);

        $this->assertStringNotContainsString('SupportCompiledWireKeys::openLoop(', $compiled);
        $this->assertStringNotContainsString('SupportCompiledWireKeys::startLoop(', $compiled);
        $this->assertStringNotContainsString('SupportCompiledWireKeys::endLoop(', $compiled);
        $this->assertStringNotContainsString('SupportCompiledWireKeys::closeLoop(', $compiled);
    }

    public function test_conditional_markers_are_still_output_when_compiled_wire_keys_are_disabled()
    {
        $compiled = $this->compile(<<<'HTML'
        <div>
            @if(true)
                foo
            @endif
        </div>
        HTML);

        $this->assertStringContainsString('<!--[if BLOCK]><![endif]-->', $compiled);
        $this->assertStringContainsString('<!--[if ENDBLOCK]><![endif]-->', $compiled);
    }

    protected function compile($string)
    {
        $html = Blade::compileString($string);

        return $html;
    }
}
