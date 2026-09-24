<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

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

    public function test_validating_encrypted_model_attribute_uses_decrypted_value()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, EncryptedPhoneValidationComponent::class)
                ->type('@phone', '111 111-1111')
                ->waitForLivewire()->click('@save')
                ->assertDontSee('@message')
                ->type('@phone', 'short')
                ->waitForLivewire()->click('@save')
                ->assertSeeIn('@message', 'The phone field must be at least 12 characters.');
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

class EncryptedPhoneModel extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'phone' => null],
    ];

    protected $casts = ['phone' => 'encrypted'];
}

class EncryptedPhoneValidationComponent extends \Livewire\Component
{
    public ?EncryptedPhoneModel $contact;

    protected $rules = [
        'contact.phone' => 'nullable|min:12|max:12',
    ];

    public function mount()
    {
        $this->contact = EncryptedPhoneModel::first();
    }

    public function save()
    {
        $this->validate();
    }

    public function render()
    {
        return <<<'HTML'
            <div>
                <input dusk="phone" wire:model="contact.phone" />
                <button dusk="save" wire:click="save">Save</button>
                @error('contact.phone')
                    <div dusk="message">{{ $message }}</div>
                @enderror
            </div>
        HTML;
    }
}