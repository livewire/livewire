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

    public function test_synthetic_tuples_with_collection_synth_key_are_rejected_in_updates()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        // Simulate an attacker injecting a synthetic tuple with a CollectionSynth key
        // into the updates field — this is the attack vector from CVE-2025-54068.
        $component->set('publicProperty', ['malicious_data', ['s' => 'clctn']]);
    }

    public function test_synthetic_tuples_with_model_synth_key_are_rejected_in_updates()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->set('publicProperty', ['malicious_data', ['s' => 'mdl']]);
    }

    public function test_synthetic_tuples_with_form_synth_key_are_rejected_in_updates()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->set('publicProperty', ['malicious_data', ['s' => 'form']]);
    }

    public function test_nested_synthetic_tuples_are_rejected_in_updates()
    {
        $this->expectException(CorruptComponentPayloadException::class);

        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        // Synthetic tuple nested inside an outer array.
        $component->set('items', [
            'safe_value',
            ['malicious_data', ['s' => 'clctn']],
        ]);
    }

    public function test_two_element_arrays_with_non_synthesizer_s_key_are_allowed_in_updates()
    {
        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        // A 2-element array where [1] has an 's' key but it doesn't match
        // any registered synthesiser key — this should NOT be rejected.
        $component->set('items', ['foo', ['s' => 'not-a-synth-key']]);

        $this->assertEquals(['foo', ['s' => 'not-a-synth-key']], $component->get('items'));
    }

    public function test_normal_array_updates_are_not_affected_by_synthetic_tuple_check()
    {
        app('livewire')->component('security-target', SecurityTargetStub::class);
        $component = app('livewire')->test('security-target');

        $component->set('items', ['one', 'two', 'three']);

        $this->assertEquals(['one', 'two', 'three'], $component->get('items'));
    }
}

class SecurityTargetStub extends TestComponent
{
    public $publicProperty = 'foo';
    public $items = [];
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
