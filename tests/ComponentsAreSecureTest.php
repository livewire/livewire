<?php

namespace Tests;

use Livewire\Exceptions\MissingComponentMethodReferencedByAction;
use Livewire\Exceptions\NonPublicComponentMethodCall;
use Livewire\Exceptions\ProtectedPropertyBindingException;
use Livewire\Component;

class ComponentsAreSecureTest extends TestCase
{
    /** @test */
    function throws_method_not_found_exception_when_action_missing()
    {
        $this->expectException(MissingComponentMethodReferencedByAction::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->runAction('missingMethod');
    }

    /** @test */
    function can_only_call_public_methods()
    {
        $this->expectException(NonPublicComponentMethodCall::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->runAction('protectedMethod');
    }

    /** @test */
    function can_only_call_methods_defined_by_user()
    {
        $this->expectException(NonPublicComponentMethodCall::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        // "redirect" happens to be a public method defined on the base Component class.
        $component->runAction('redirect');
    }

    /** @test */
    function can_only_set_public_properties()
    {
        $this->expectException(ProtectedPropertyBindingException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->updateProperty('protectedProperty', 'bar');
    }
}

class SecurityTargetStub extends Component
{
    protected $protectedProperty = 'foo';

    protected function protectedMethod()
    {
        return;
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
