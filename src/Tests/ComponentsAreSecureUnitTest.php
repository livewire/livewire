<?php

namespace Livewire\Tests;

use Livewire\Features\SupportLifecycleHooks\DirectlyCallingLifecycleHooksNotAllowedException;
use Livewire\Mechanisms\HandleComponents\CorruptComponentPayloadException;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Exceptions\MethodNotFoundException;
use Tests\TestComponent;

class ComponentsAreSecureUnitTest extends \Tests\TestCase
{
    public function test_throws_method_not_found_exception_when_action_missing()
    {
        $this->expectException(MethodNotFoundException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->runAction('missingMethod');
    }

    public function test_can_only_call_methods_defined_by_user()
    {
        $this->expectException(MethodNotFoundException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        // "redirect" happens to be a public method defined on the base Component class.
        $component->runAction('redirect');
    }

    public function test_can_only_set_public_properties()
    {
        $this->expectException(PublicPropertyNotFoundException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->updateProperty('protectedProperty', 'baz');
    }

    public function test_data_cannot_be_tampered_with_on_frontend()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $snapshot = $component->snapshot;

        $snapshot['data']['0']['publicProperty'] = 'different-property';

        $component->snapshot = $snapshot;

        $component->call('$refresh');
    }

    public function test_id_cannot_be_tampered_with_on_frontend()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $snapshot = $component->snapshot;

        $snapshot['memo']['id'] = 'different-id';

        $component->snapshot = $snapshot;

        $component->call('$refresh');
    }

    public function test_component_name_cannot_be_tampered_with_on_frontend()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('safe', SecurityTargetStub::class);
        app('livewire')->component('unsafe', UnsafeComponentStub::class);
        $component = app('livewire')->test('safe');

        $snapshot = $component->snapshot;

        // Hijack the "safe" component, with "unsafe"
        $snapshot['memo']['name'] = 'unsafe';

        $component->snapshot = $snapshot;

        // If the hijack was stopped, the expected exception will be thrown.
        // If it worked, then an exception will be thrown that will fail the test.
        $component->runAction('someMethod');
    }

    public function test_cannot_call_mount_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('mount');
    }

    public function test_cannot_call_boot_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('boot');
    }

    public function test_cannot_call_booted_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('booted');
    }

    public function test_cannot_call_hydrate_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('hydrate');
    }

    public function test_cannot_call_dehydrate_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('dehydrate');
    }

    public function test_cannot_call_updating_hook_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('updatingName');
    }

    public function test_cannot_call_updated_hook_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('updatedName');
    }

    public function test_cannot_call_rendering_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('rendering');
    }

    public function test_cannot_call_rendered_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('rendered');
    }

    public function test_cannot_call_exception_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('exception');
    }

    public function test_cannot_call_trait_mount_variant_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target-with-trait', LifecycleMethodStubWithTrait::class);
        $component = app('livewire')->test('lifecycle-target-with-trait');

        $component->runAction('mountStubTrait');
    }

    public function test_cannot_call_trait_boot_variant_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target-with-trait', LifecycleMethodStubWithTrait::class);
        $component = app('livewire')->test('lifecycle-target-with-trait');

        $component->runAction('bootStubTrait');
    }

    public function test_cannot_call_trait_booted_variant_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target-with-trait', LifecycleMethodStubWithTrait::class);
        $component = app('livewire')->test('lifecycle-target-with-trait');

        $component->runAction('bootedStubTrait');
    }

    public function test_cannot_call_hydrate_property_variant_from_frontend()
    {
        $this->expectException(DirectlyCallingLifecycleHooksNotAllowedException::class);

        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        $component->runAction('hydratePropertyName');
    }

    public function test_can_call_methods_that_share_lifecycle_prefix_but_are_not_lifecycle_hooks()
    {
        app('livewire')->component('lifecycle-target', LifecycleMethodStub::class);
        $component = app('livewire')->test('lifecycle-target');

        // mountAction is a regular user method, not a lifecycle hook.
        // It should not be blocked despite starting with "mount".
        $component->runAction('mountAction');

        $this->assertTrue(true);
    }

}

class SecurityTargetStub extends TestComponent
{
    public $publicProperty = 'foo';
    protected $protectedProperty = 'bar';

    public function publicMethod()
    {
    }

    protected function protectedMethod()
    {
    }
}

class UnsafeComponentStub extends TestComponent
{
    public function someMethod()
    {
        throw new \Exception('Should not be able to acess me!');
    }
}

class LifecycleMethodStub extends TestComponent
{
    public $name = 'foo';

    public function mount() {}
    public function boot() {}
    public function booted() {}
    public function hydrate() {}
    public function dehydrate() {}
    public function updatingName() {}
    public function updatedName() {}
    public function rendering() {}
    public function rendered() {}
    public function exception($e) {}
    public function hydratePropertyName() {}

    public function mountAction() {}
    public function saveData() {}
}

trait StubTrait
{
    //
}

class LifecycleMethodStubWithTrait extends TestComponent
{
    use StubTrait;

    public $name = 'foo';

    public function mountStubTrait() {}
    public function bootStubTrait() {}
    public function bootedStubTrait() {}

    public function saveData() {}
}
