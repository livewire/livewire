<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\ExpectationFailedException;

class TestableLivewireViewBindingsTest extends TestCase
{
    /**
     * @dataProvider propertyBindingDataProvider
     */
    public function test_it_asserts_a_wire_model_binding(string $propertyName, ?int $timesBound = null, bool $expectException = false)
    {
        if($expectException) {
            $this->expectException(ExpectationFailedException::class);
        }

        Livewire::test(PropertyBindingTestingComponent::class)
            ->assertPropertyBound($propertyName, $timesBound);
    }

    public function test_it_asserts_unbound_properties()
    {
        Livewire::test(PropertyBindingTestingComponent::class)
            ->assertPropertyNotBound('doesnotexist');
    }

    public function test_it_asserts_a_conditionally_bound_property()
    {
        Livewire::test(PropertyBindingTestingComponent::class)
            ->assertPropertyNotBound('only_if_some_condition')
            ->set('someCondition', true)
            ->assertPropertyBound('only_if_some_condition');
    }

    /**
     * @dataProvider actionBindingDataProvider
     */
    public function test_it_asserts_an_action_binding(string $actionName, ?string $eventName = null, ?int $timesBound = null, bool $expectException = false)
    {
        if($expectException) {
            $this->expectException(ExpectationFailedException::class);
        }

        Livewire::test(PropertyBindingTestingComponent::class)
            ->assertActionBound($actionName, $eventName, $timesBound);
    }

    public function test_it_asserts_unbound_actions()
    {
        Livewire::test(PropertyBindingTestingComponent::class)
            ->assertActionNotBound('never');
    }

    public function test_it_asserts_a_conditionally_bound_action()
    {
        Livewire::test(PropertyBindingTestingComponent::class)
            ->assertActionNotBound('only_if_some_condition')
            ->set('someCondition', true)
            ->assertActionBound('only_if_some_condition');
    }

    public function propertyBindingDataProvider(): array
    {
        return [
            'Property "never" bound at least once, expect exception' => ['never', null, true],
            'Property "never" bound exactly 0 times, no exception' => ['never', 0, false],
            'Property "never" bound exactly once, expect exception' => ['never', 1, true],
            'Property "never" bound exactly twice, expect exception' => ['never', 2, true],

            'Property "once" bound at least once, no exception' => ['once', null, false],
            'Property "once" bound exactly 0 times, expect exception' => ['once', 0, true],
            'Property "once" bound exactly once, no exception' => ['once', 1, false],
            'Property "once" bound exactly twice, expect exception' => ['once', 2, true],

            'Property "twice" bound at least once, no exception' => ['twice', null, false],
            'Property "twice" bound exactly 0 times, expect exception' => ['twice', 0, true],
            'Property "twice" bound exactly 1 time, expect exception' => ['twice', 1, true],
            'Property "twice" bound exactly twice, no exception' => ['twice', 2, false],

            'Property "debounced" (Debounced) bound at least once, no exception' => ['debounced', null, false],
            'Property "debounced" (Debounced 500ms) bound at least once, no exception' => ['debounced', null, false],

            'Property "defer.+<>ed" bound once, no exception' => ['defer.+<>ed', 1, false],
        ];
    }

    public function actionBindingDataProvider(): array
    {
        return [
            'Action "never" for any eventName bound at least once, expect exception' => ['never', null, null, true],
            'Action "never" for any eventName bound exactly 0 times, no exception' => ['never', null, 0, false],
            'Action "never" for any eventName bound exactly once, expect exception' => ['never', null, 1, true],
            'Action "never" for any eventName bound exactly twice, expect exception' => ['never', null, 2, true],
            'Action "never" for "click" event bound at least once, expect exception' => ['never', 'click', null, true],
            'Action "never" for "click" event bound exactly 0 times, no exception' => ['never', 'click', 0, false],
            'Action "never" for "click" event bound exactly once, expect exception' => ['never', 'click', 1, true],
            'Action "never" for "click" event bound exactly twice, expect exception' => ['never', 'click', 2, true],

            'Action "once" for any eventName bound at least once, no exception' => ['once', null, null, false],
            'Action "once" for any eventName bound exactly 0 times, expect exception' => ['once', null, 0, true],
            'Action "once" for any eventName bound exactly once, no exception' => ['once', null, 1, false],
            'Action "once" for any eventName bound exactly twice, expect exception' => ['once', null, 2, true],
            'Action "once" for "click" event bound at least once, no exception' => ['once', 'click', null, false],
            'Action "once" for "click" event bound exactly 0 times, expect exception' => ['once', 'click', 0, true],
            'Action "once" for "click" event bound exactly once, no exception' => ['once', 'click', 1, false],
            'Action "once" for "click" event bound exactly twice, expect exception' => ['once', 'click', 2, true],

            'Action "twice" for any eventName bound at least once, no exception' => ['twice', null, null, false],
            'Action "twice" for any eventName bound exactly 0 times, expect exception' => ['twice', null, 0, true],
            'Action "twice" for any eventName bound exactly once, expect exception' => ['twice', null, 1, true],
            'Action "twice" for any eventName bound exactly twice, no exception' => ['twice', null, 2, false],

            'Action "action_on_enter" for "keydown" event bound exactly once, no exception' => ['action_on_enter', 'keydown', 1, false],
            'Action "action_on_enter" for "keydown.enter" event bound exactly once, no exception' => ['action_on_enter', 'keydown.enter', 1, false],
        ];
    }
}

class PropertyBindingTestingComponent extends Component
{
    public $someCondition = false;

    public function render(): string
    {
        return <<<'blade'
            <div>
                <input type="text" wire:model="once"/>
                <input type="text" wire:model="twice"/>
                <input type="date" wire:model="twice"/>
                <input type="date" wire:model="once.nested"/>
                <input type="text" wire:model.debounce="debounced"/>
                <input type="text" wire:model.debounce.500ms="debounced_500ms"/>
                <input type="text" wire:model.lazy="lazy"/>
                <input type="text" wire:model.defer="defered"/>
                <input type="text" wire:model.defer="defer.+<>ed"/>
                @if($someCondition)
                    <input type="text" wire:model="only_if_some_condition"/>
                    <button type="button" wire:click="only_if_some_condition"/>
                @endif
                <button type="button" id="once" wire:click="once"></button>
                <button type="button" id="twice" wire:click="twice"></button>
                <button type="button" id="twice_2" wire:click="twice"></button>
                <button wire:keydown.enter="action_on_enter"></button>
            </div>
blade;
    }
}
