<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutImplementingWireableException;
use Livewire\Hydration\PublicPropertyManager;
use Livewire\Livewire;
use Livewire\Wireable;

class PublicPropertyManagerTest extends TestCase
{
    /** @test */
    public function the_manager_will_be_available_inside_the_service_provider()
    {
        $this->assertInstanceOf(PublicPropertyManager::class, app(PublicPropertyManager::class));
    }

    /** @test */
    public function it_will_throw_an_exception_if_registering_a_class_not_implementing_the_wireable_interface()
    {
        $this->expectException(CannotRegisterPublicPropertyWithoutImplementingWireableException::class);

        $resolver = new class() {};

        app(PublicPropertyManager::class)->register('className', $resolver);
    }

    /** @test */
    public function a_custom_property_class_does_take_affect()
    {
        app(PublicPropertyManager::class)->register(CustomPublicClass::class, CustomResolverClass::class);

        $custom = new CustomPublicClass($message = Str::random(), $embeddedMessage = Str::random());

        Livewire::test(ComponentWithCustomPublicProperty::class, ['wireable' => $custom])
            ->assertSee($message);
//            ->assertSee($embeddedMessage)
//            ->call('$refresh')
//            ->assertSee($message);
//            ->assertSee($embeddedMessage)
//            ->call('removeWireable')
//            ->assertDontSee($message);
//            ->assertDontSee($embeddedMessage);
    }

    /** @test */
    public function a_registered_custom_public_property_will_be_registered()
    {
        app(PublicPropertyManager::class)->register(
            CustomPublicClass::class,
            CustomResolverClass::class
        );

        $this->assertCount(1, app(PublicPropertyManager::class)->properties());
    }
}

class CustomPublicClass
{
    public $message;

    public function __construct($message, $embeddedMessage)
    {
        $this->message = $message;
    }
}

class CustomResolverClass implements Wireable
{
    public function toLivewire()
    {
        dd('toLivewire');
    }

    public static function fromLivewire($value)
    {
        dd('fromLivewire');
    }
}

class ComponentWithCustomPublicProperty extends Component
{
    public CustomPublicClass $wireable;

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
