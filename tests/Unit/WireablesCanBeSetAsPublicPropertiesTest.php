<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Livewire\Component;
use Livewire\Wireable;

class WireablesCanBeSetAsPublicPropertiesTest extends TestCase
{
    /** @test */
    public function a_wireable_can_be_set_as_a_public_property()
    {
        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
            ->assertSee($embeddedMessage);
    }

    /** @test */
    public function a_wireable_can_be_set_then_removed_as_a_public_property()
    {
        $wireable = new WireableClass($message = Str::random(), $embeddedMessage = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->assertSee($embeddedMessage)
            ->call('removeWireable')
            ->assertDontSee($message)
            ->assertDontSee($embeddedMessage);
    }

    /** @test */
    public function a_wireable_can_use_custom_serialization()
    {
        $wireable = new WireableClassWithCustomSerialization($message = Str::random());

        Livewire::test(ComponentWithWireablePublicProperty::class, ['wireable' => $wireable])
            ->assertSee($message)
            ->call('$refresh')
            ->assertSee($message);
    }
}

class WireableClass
{
    use Wireable;

    public $message;

    public $embeddedWireable;

    public function __construct($message, $embeddedMessage)
    {
        $this->message = $message;
        $this->embeddedWireable = new EmbeddedWireableClass($embeddedMessage);
    }
}

class EmbeddedWireableClass
{
    use Wireable;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }
}

class WireableClassWithCustomSerialization
{
    use Wireable;

    public $message;

    public function toLivewire()
    {
        return [
            'message' => $this->message,
        ];
    }

    public static function fromLivewire($value): self
    {
        $self = new self();
        $self->message = $value['message'];

        return $self;
    }
}

class ComponentWithWireablePublicProperty extends Component
{
    public $wireable;

    public function removeWireable()
    {
        $this->wireable = null;
    }

    public function render()
    {
        return view('wireables');
    }
}
