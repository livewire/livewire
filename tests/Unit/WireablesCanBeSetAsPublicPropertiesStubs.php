<?php

namespace Tests\Unit;

use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Wireable;

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
        return view('wireables');
    }
}

class ValidatesWireableProperty extends Component
{
    public CustomWireableCollection $customCollection;

    public $rules = [
        'customCollection.*.amount' => 'required|gt:100'
    ];

    public function mount()
    {
        $this->customCollection = new CustomWireableCollection([
            new CustomWireableDTO(50),
        ]);
    }

    public function runValidation()
    {
        $this->validate();
    }

    public function render()
    {
        return view('null-view');
    }
}

class CustomWireableCollection extends Collection implements Wireable
{
    public function toLivewire()
    {
        return $this->mapWithKeys(function($dto, $key) {
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
