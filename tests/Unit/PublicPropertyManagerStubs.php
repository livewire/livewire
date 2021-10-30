<?php

namespace Tests\Unit;

use Livewire\Component;

class CustomPublicClass
{
    public $message;

    public function __construct($message, $embeddedMessage)
    {
        $this->message = $message;
    }
}

class CustomResolverClass
{
    public ?CustomPublicClass $class;

    public function __construct(CustomPublicClass $class)
    {
        $this->class = $class;
    }

    public static function fromLivewire($value)
    {
        return new CustomPublicClass($value['message'], 'embedded message which is missing right now');
    }

    public function toLivewire()
    {
        return [
            'message' => $this->class->message,
        ];
    }
}

class ComponentWithCustomPublicProperty extends Component
{
    public ?CustomPublicClass $wireable;

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
