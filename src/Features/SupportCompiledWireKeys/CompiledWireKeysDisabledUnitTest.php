<?php

namespace Livewire\Features\SupportCompiledWireKeys;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportMorphAwareBladeCompilation\SupportMorphAwareBladeCompilation;
use Livewire\Livewire;
use Livewire\Mechanisms\ExtendBlade\ExtendBlade;

// This test needs to be in its own file, because if we try to disable the feature
// in the same test as the feature enabled test the test will fail because the
// precompiler will have already been registered....
class CompiledWireKeysDisabledUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Livewire::flushState();

        config()->set('livewire.smart_wire_keys', false);

        // We need to call these so provide gets called again to load the new config...
        ComponentHookRegistry::register(SupportMorphAwareBladeCompilation::class);
        ComponentHookRegistry::register(SupportCompiledWireKeys::class);
    }

    public function test_keys_are_not_compiled_when_compiled_wire_keys_is_disabled()
    {
        $compiled = $this->compile('<div wire:key="foo">');

        $this->assertStringNotContainsString('<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey', $compiled);
    }

    protected function compile($string)
    {
        $undo = app(ExtendBlade::class)->livewireifyBladeCompiler();

        $html = Blade::compileString($string);

        $undo();

        return $html;
    }
}
