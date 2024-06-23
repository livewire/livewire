<?php

namespace Livewire\Features\SupportWireables;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Wireable;

class UnitTest extends \Tests\TestCase
{
    public function test_a_wireable_can_be_set_as_a_public_property_and_validates()
    {
        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidation')
            ->assertHasNoErrors(['wireable.message', 'wireable.embeddedWireable.message'])
            ->call('removeWireable')
            ->assertDontSee($message)
            ->assertDontSee($embeddedMessage);
    }

    public function test_a_wireable_can_be_updated()
    {
        $wireable = new WireableClass('foo', '42');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee('foo')
            ->call("\$set", 'wireable', ['message' => 'bar', 'embeddedWireable' => ['message' => '42']])
            ->call("\$set", 'wireable.message', 'bar')
            ->assertSee('bar');
    }

    public function test_a_wireable_can_be_set_as_a_public_property_and_validates_only()
    {
        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.message')
            ->assertHasNoErrors('wireable.message')
            ->call('runValidateOnly', 'wireable.embeddedWireable.message')
            ->assertHasNoErrors('wireable.embeddedWireable.message')
            ->call('removeWireable')
            ->assertDontSee($message)
            ->assertDontSee($embeddedMessage);
    }

    public function test_a_wireable_can_be_set_as_a_public_property_and_has_single_validation_error()
    {
        $wireable = new WireableClass($message = '', $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidation')
            ->assertHasErrors(['wireable.message' => 'required'])
            ->assertHasNoErrors('wireable.embeddedWireable.message')
            ->call('removeWireable')
            ->assertDontSee($embeddedMessage);
    }

    public function test_a_wireable_can_be_set_as_a_public_property_and_has_single_validation_error_on_validates_only()
    {
        $wireable = new WireableClass($message = '', $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.message')
            ->assertHasErrors(['wireable.message' => 'required'])
            ->assertHasNoErrors('wireable.embeddedWireable.message')
            ->call('removeWireable')
            ->assertDontSee($embeddedMessage);
    }

    public function test_a_wireable_can_be_set_as_a_public_property_and_has_embedded_validation_error()
    {
        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = '');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidation')
            ->assertHasErrors(['wireable.embeddedWireable.message' => 'required'])
            ->assertHasNoErrors('wireable.message')
            ->call('removeWireable')
            ->assertDontSee($message);
    }

    public function test_a_wireable_can_be_set_as_a_public_property_and_has_embedded_validation_error_on_validate_only()
    {
        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = '');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.embeddedWireable.message')
            ->assertHasErrors(['wireable.embeddedWireable.message' => 'required'])
            ->assertHasNoErrors('wireable.message')
            ->call('removeWireable')
            ->assertDontSee($message);
    }

    public function test_a_wireable_can_be_set_as_a_public_property_and_has_single_and_embedded_validation_errors()
    {
        $wireable = new WireableClass($message = '', $embeddedMessage = '');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidation')
            ->assertHasErrors(['wireable.message' => 'required', 'wireable.embeddedWireable.message' => 'required'])
            ->call('removeWireable');
    }

    public function test_a_wireable_can_be_set_as_a_public_property_and_has_single_and_embedded_validation_errors_on_validate_only()
    {
        $wireable = new WireableClass($message = '', $embeddedMessage = '');

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.message')
            ->assertHasErrors(['wireable.message' => 'required'])
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('runValidateOnly', 'wireable.embeddedWireable.message')
            ->assertHasErrors(['wireable.embeddedWireable.message' => 'required'])
            ->call('removeWireable');
    }
}

class WireableClass implements Wireable
{
    public $message;

    public EmbeddedWireableClass $embeddedWireable;

    public function __construct($message, $embeddedMessage)
    {
        $this->message = $message;
        $this->embeddedWireable = new EmbeddedWireableClass($embeddedMessage);
    }

    public function toLivewire()
    {
        return [
            'message' => $this->message,
            'embeddedWireable' => $this->embeddedWireable->toLivewire(),
        ];
    }

    public static function fromLivewire($value): self
    {
        return new self($value['message'], $value['embeddedWireable']['message']);
    }
}

class EmbeddedWireableClass implements Wireable
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function toLivewire()
    {
        return [
            'message' => $this->message,
        ];
    }

    public static function fromLivewire($value): self
    {
        return new self($value['message']);
    }
}

class ComponentWithWireablePublicProperty extends Component
{
    public ?WireableClass $wireable;

    public $rules = [
        'wireable.message' => 'string|required',
        'wireable.embeddedWireable.message' => 'string|required'
    ];

    public function mount($wireable)
    {
        $this->wireable = $wireable;
    }

    public function runValidation()
    {
        $this->validate();
    }

    public function runValidateOnly($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function removeWireable()
    {
        $this->resetErrorBag();
        $this->wireable = null;
    }

    public function runResetValidation()
    {
        $this->resetValidation();
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <div>
                @if ($wireable)
                    {{ $wireable->message }}

                    @if ($wireable->embeddedWireable ?? false)
                        {{ $wireable->embeddedWireable->message }}
                    @endif
                @endif
            </div>
        </div>
        HTML;
    }
}

class CustomWireableCollection extends Collection implements Wireable
{
    public function toLivewire()
    {
        return $this->mapWithKeys(function ($dto, $key) {
            return [$key => $dto instanceof CustomWireableDTO ? $dto->toLivewire() : $dto];
        })->all();
    }

    public static function fromLivewire($value)
    {
        return static::wrap($value)
        ->mapWithKeys(function ($dto, $key) {
            return [$key => CustomWireableDTO::fromLivewire($dto)];
        });
    }
}

class CustomWireableDTO implements Wireable
{
    public $amount;

    public function __construct($amount)
    {
        $this->amount = $amount;
    }

    public function toLivewire()
    {
        return [
            'amount' => $this->amount
        ];
    }

    public static function fromLivewire($value)
    {
        return new static(
            $value['amount']
        );
    }
}
