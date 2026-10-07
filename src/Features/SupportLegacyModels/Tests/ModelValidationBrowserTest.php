<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Illuminate\Validation\ValidationException;
use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;

class ModelValidationBrowserTest extends TestCase
{
    use Concerns\EnableLegacyModels;

    public function test_validating_casted_model_attribute_throws_validation_exception_on_wrong_value_type()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, ModelValidationComponent::class)
                ->assertValue('@age', '40')
                ->type('@age', '32.5')
                ->assertValue('@age', '32.5')
                ->waitForLivewire()->click('@save')
                ->assertSeeIn('@message', 'The age field must be an integer.');
        });
    }
}

class ModelValidationUser extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'name' => 'Bob', 'age' => 40],
    ];

    protected $casts = ['age' => 'integer'];
}

class ModelValidationComponent extends \Livewire\Component
{
    public ?ModelValidationUser $foo;

    protected $rules = [
        'foo.age' => 'required|integer'
    ];

    public function mount()
    {
        $this->foo = ModelValidationUser::first();
    }

    public function save()
    {
        $this->validate();
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                <input dusk="age" wire:model="foo.age" />
                <button dusk="save" wire:click="save">Save</button>
                @error('foo.age')
                    <div dusk="message">{{ $message }}</div>
                @enderror
            </div>
        HTML;
    }
}