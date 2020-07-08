<?php

namespace Tests;

use Livewire\Livewire;
use Livewire\Component;
use Livewire\Exceptions\MissingRulesPropertyException;

class ComponentHasRulesPropertyTest extends TestCase
{
    /** @test */
    public function validate_with_rules_property()
    {
        Livewire::test(ComponentWithRulesProperty::class)
            ->set('foo', '')
            ->call('save')
            ->assertHasErrors(['foo' => 'required']);
    }

    /** @test */
    public function validate_only_with_rules_property()
    {
        Livewire::test(ComponentWithRulesProperty::class)
            ->set('bar', '')
            ->assertHasErrors(['bar' => 'required']);
    }

    /** @test */
    public function validate_without_rules_property_and_no_args_throws_exception()
    {
        $this->expectException(MissingRulesPropertyException::class);

        Livewire::test(ComponentWithoutRulesProperty::class)->call('save');
    }
}

class ComponentWithRulesProperty extends Component
{
    public $foo;
    public $bar = 'baz';

    protected $rules = [
        'foo' => 'required',
        'bar' => 'required',
    ];

    public function updatedBar()
    {
        $this->validateOnly('bar');
    }

    public function save()
    {
        $this->validate();
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ComponentWithoutRulesProperty extends Component
{
    public $foo;

    public function save()
    {
        $this->validate();
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
