<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutImplementingWireableException;
use Livewire\LivewirePropertyManager;
use Livewire\Livewire;

class PublicPropertyManagerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkippede('Skip public property tests if the version is below PHP 7.4');
        }

        require_once __DIR__.'/PublicPropertyManagerStubs.php';
    }

    /** @test */
    public function the_manager_will_be_available_inside_the_service_provider()
    {
        $this->assertInstanceOf(LivewirePropertyManager::class, app(LivewirePropertyManager::class));
    }

    /** @test */
    public function it_will_throw_an_exception_if_registering_a_class_not_implementing_the_wireable_interface()
    {
        $this->markTestSkipped("We will throw an exception, but I guess we'll need a new interface. Until then, do nothing.");

        $this->expectException(CannotRegisterPublicPropertyWithoutImplementingWireableException::class);

        $resolver = new class() {};

        app(LivewirePropertyManager::class)->register('className', $resolver);
    }

    /** @test */
    public function a_custom_property_class_does_take_affect()
    {
        app(LivewirePropertyManager::class)->register(CustomPublicClass::class, CustomResolverClass::class);

        $custom = new CustomPublicClass($message = Str::random(), $embeddedMessage = Str::random());

        Livewire::test(ComponentWithCustomPublicProperty::class, ['wireable' => $custom])
            ->assertSee($message)
//            ->assertSee($embeddedMessage)
            ->call('$refresh')
            ->assertSee($message)
//            ->assertSee($embeddedMessage)
            ->call('removeWireable')
            ->assertDontSee($message);
//            ->assertDontSee($embeddedMessage);
    }

    /** @test */
    public function a_registered_custom_public_property_will_be_registered()
    {
        app(LivewirePropertyManager::class)->register(
            CustomPublicClass::class,
            CustomResolverClass::class
        );

        $this->assertCount(1, app(LivewirePropertyManager::class)->properties());
    }
}
