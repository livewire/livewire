<?php

namespace Tests\Unit;

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

    public function mount($wireable)
    {
        $this->wireable = $wireable;
    }

    public function removeWireable()
    {
        $this->wireable = null;
    }

    public function render()
    {
        return view('wireables');
    }
}
