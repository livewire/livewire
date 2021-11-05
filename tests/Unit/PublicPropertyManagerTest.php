<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutExtendingThePropertyHandlerException;
use Livewire\LivewireProperty;
use Livewire\Livewire;

class PublicPropertyManagerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->markTestSkipped('Skip public property tests if the version is below PHP 7.4');
        }

        require_once __DIR__.'/PublicPropertyManagerStubs.php';
    }

    /** @test */
    public function it_will_throw_an_exception_if_registering_a_class_not_implementing_the_property_handler_interface()
    {
        $this->expectException(CannotRegisterPublicPropertyWithoutExtendingThePropertyHandlerException::class);

        $resolver = new class() {};

        LivewireProperty::register('className', $resolver);
    }

    /** @test */
    public function a_custom_property_class_does_take_affect()
    {
        LivewireProperty::register(CustomPublicClass::class, CustomResolverClass::class);

        $custom = new CustomPublicClass($message = Str::random());

        Livewire::test(ComponentWithCustomPublicProperty::class, ['wireable' => $custom])
            ->assertSee($message)
            ->call('$refresh')
            ->assertSee($message)
            ->call('removeWireable')
            ->assertDontSee($message);
    }

    /** @test */
    public function a_registered_custom_public_property_will_be_registered()
    {
         LivewireProperty::register(
            CustomPublicClass::class,
            CustomResolverClass::class
        );

        $this->assertCount(1, LivewireProperty::properties());
    }
}
