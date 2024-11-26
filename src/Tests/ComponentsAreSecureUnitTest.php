<?php

namespace Livewire\Tests;

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
        $this->markTestSkipped(); // @todo: This needs to be fixed.
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
        $this->markTestSkipped(); // @todo: This needs to be fixed.
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
        $this->markTestSkipped(); // @todo: This needs to be fixed.
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
