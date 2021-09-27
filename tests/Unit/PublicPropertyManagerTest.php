<?php

namespace Tests\Unit;

use Livewire\Exceptions\CannotRegisterPublicPropertyWithoutImplementingWireableException;
use Livewire\Hydration\PublicPropertyManager;
use Livewire\Wireable;

class PublicPropertyManagerTest extends TestCase
{
    /** @test */
    public function the_manager_will_be_available_inside_the_service_provider()
    {
        $manager = app(PublicPropertyManager::class);

        $this->assertInstanceOf(PublicPropertyManager::class, $manager);
    }

    /** @test */
    public function it_will_throw_an_exception_if_registering_a_class_not_implementing_the_wireable_interface()
    {
        $this->expectException(CannotRegisterPublicPropertyWithoutImplementingWireableException::class);

        $class = new class() {};

        app(PublicPropertyManager::class)->register($class);
    }

    /** @test */
    public function it_will_throw_an_exception_if_registering_a_class_not_implementing_the_wireable_interfacddddde()
    {
        $class = new class() implements Wireable {
            public function toLivewire() {}
            public static function fromLivewire($value) {}
        };

        $manager = app(PublicPropertyManager::class)->register($class);

        $this->assertCount(1, $manager->properties);
    }
}
