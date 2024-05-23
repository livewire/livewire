<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Livewire\Livewire;
use Tests\TestComponent;

class CarbonSynthUnitTest extends \Tests\TestCase
{
    public function test_public_carbon_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithPublicCarbonCaster::class)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', Carbon::parse('2024-02-14'));

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    public function test_public_nullable_carbon_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicCarbonCaster::class)
            ->assertSetStrict('date', null)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', Carbon::parse('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSetStrict('date', null)
            ->updateProperty('date', null)
            ->assertSetStrict('date', null);

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    public function test_public_carbon_immutable_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicCarbonImmutableCaster::class)
            ->assertSetStrict('date', null)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', CarbonImmutable::parse('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSetStrict('date', null)
            ->updateProperty('date', null)
            ->assertSetStrict('date', null);

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    public function test_public_datetime_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicDateTimeCaster::class)
            ->assertSetStrict('date', null)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', new \DateTime('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSetStrict('date', null)
            ->updateProperty('date', null)
            ->assertSetStrict('date', null);

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    public function test_public_datetime_immutable_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicDateTimeImmutableCaster::class)
            ->assertSetStrict('date', null)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', new \DateTimeImmutable('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSetStrict('date', null)
            ->updateProperty('date', null)
            ->assertSetStrict('date', null);

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }
}

class ComponentWithPublicCarbonCaster extends TestComponent
{
    public Carbon $date;
}

class ComponentWithNullablePublicCarbonCaster extends TestComponent
{
    public ?Carbon $date = null;
}

class ComponentWithNullablePublicCarbonImmutableCaster extends TestComponent
{
    public ?CarbonImmutable $date = null;
}

class ComponentWithNullablePublicDateTimeCaster extends TestComponent
{
    public ?\DateTime $date = null;
}
class ComponentWithNullablePublicDateTimeImmutableCaster extends TestComponent
{
    public ?\DateTimeImmutable $date = null;
}
