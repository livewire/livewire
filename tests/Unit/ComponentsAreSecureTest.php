<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Exceptions\NonPublicComponentMethodCall;
use Livewire\Exceptions\CorruptComponentPayloadException;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Exceptions\MethodNotFoundException;

class ComponentsAreSecureTest extends TestCase
{
    /** @test */
    public function throws_method_not_found_exception_when_action_missing()
    {
        $this->expectException(MethodNotFoundException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->runAction('missingMethod');
    }

    /** @test */
    public function can_only_call_public_methods()
    {
        $this->expectException(NonPublicComponentMethodCall::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->runAction('protectedMethod');
    }

    /** @test */
    public function can_only_call_methods_defined_by_user()
    {
        $this->expectException(NonPublicComponentMethodCall::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        // "redirect" happens to be a public method defined on the base Component class.
        $component->runAction('redirect');
    }

    /** @test */
    public function can_only_set_public_properties()
    {
        $this->expectException(PublicPropertyNotFoundException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->updateProperty('protectedProperty', 'baz');
    }

    /** @test */
    public function data_cannot_be_tampered_with_on_frontend()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->payload['serverMemo']['data']['publicProperty'] = 'different-property';

        $component->call('$refresh');
    }

    /** @test */
    public function id_cannot_be_tampered_with_on_frontend()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->payload['fingerprint']['id'] = 'different-id';

        $component->call('$refresh');
    }

    /** @test */
    public function component_name_cannot_be_tampered_with_on_frontend()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('safe', SecurityTargetStub::class);
        app('livewire')->component('unsafe', UnsafeComponentStub::class);
        $component = app('livewire')->test('safe');

        // Hijack the "safe" component, with "unsafe"
        $component->payload['fingerprint']['name'] = 'unsafe';

        // If the hijack was stopped, the expected exception will be thrown.
        // If it worked, then an exception will be thrown that will fail the test.
        $component->runAction('someMethod');
    }
}

class SecurityTargetStub extends Component
{
    public $publicProperty = 'foo';
    protected $protectedProperty = 'bar';

    public function publicMethod()
    {
    }

    protected function protectedMethod()
    {
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class UnsafeComponentStub extends Component
{
    public function someMethod()
    {
        throw new \Exception('Should not be able to acess me!');
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
