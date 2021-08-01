<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Exceptions\MissingRulesException;

class ComponentHasFormRequestPropertyTest extends TestCase
{
    /** @test */
    public function validate_with_form_request_property()
    {
        Livewire::test(ComponentWithFormRequestProperty::class)
            ->set('foo', '')
            ->call('save')
            ->assertHasErrors(['foo' => 'required']);
    }

    /** @test */
    public function validate_only_with_form_request_property()
    {
        Livewire::test(ComponentWithFormRequestProperty::class)
            ->set('bar', '')
            ->assertHasErrors(['bar' => 'required']);
    }

    /** @test */
    public function validate_without_form_request_property_and_no_args_throws_exception()
    {
        $this->expectException(MissingRulesException::class);

        Livewire::test(ComponentWithoutFormRequestProperty::class)->call('save');
    }
}

class AttributesRequest extends FormRequest
{
    public function rules()
    {
        return [
            'foo' => 'required',
            'bar' => 'required',
            'baz.*.foo' => 'numeric',
        ];
    }
}

class ComponentWithFormRequestProperty extends Component
{
    public $foo;
    public $bar = 'baz';
    public $baz;

    protected $formRequest = AttributesRequest::class;

    public function mount()
    {
        $this->baz = collect([
            ['foo' => 'a'],
            ['foo' => 'b'],
        ]);
    }

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

class ComponentWithoutFormRequestProperty extends Component
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
