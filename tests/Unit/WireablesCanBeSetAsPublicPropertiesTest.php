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
