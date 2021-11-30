<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
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

class CustomPublicClass2
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

class CustomResolverClass2 extends PropertyHandler
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

class TestUser extends Model {}

class ComponentWithCustomPublicProperty extends Component
{
    public ?CustomPublicClass $wireable;

    public $rules = [
        'wireable.message' => 'string|required',
    ];

    public function mount($wireable)
    {
        $this->wireable = $wireable;
    }

    public function removeWireable()
    {
        $this->wireable = null;
    }

    public function runValidation()
    {
        $this->validate();
    }

    public function render()
    {
        return view('wireables');
    }
}
