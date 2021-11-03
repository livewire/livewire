<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\PropertyHandler;

class CustomPublicClass
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}

class CustomResolverClass extends PropertyHandler
{
    public ?CustomPublicClass $class;

    public function dehydrate($value)
    {
        return $value;
    }

    public static function hydrate($value)
    {
        return new CustomPublicClass($value->message);
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
