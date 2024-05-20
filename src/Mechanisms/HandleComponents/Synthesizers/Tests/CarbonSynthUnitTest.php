<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Livewire\Component;
use Livewire\Livewire;

class CarbonSynthUnitTest extends \Tests\TestCase
{
    public function test_public_carbon_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithPublicCarbonCaster::class)
            ->updateProperty('date', '2024-02-14')
            ->assertSetStrict('date', Carbon::parse('2024-02-14'));

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    public function test_public_nullable_carbon_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicCarbonCaster::class)
            ->assertSetStrict('date', null)
            ->updateProperty('date', '2024-02-14')
            ->assertSetStrict('date', Carbon::parse('2024-02-14'))
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
            ->assertSetStrict('date', CarbonImmutable::parse('2024-02-14'))
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
            ->assertSetStrict('date', new \DateTime('2024-02-14'))
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
            ->assertSetStrict('date', new \DateTimeImmutable('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSetStrict('date', null)
            ->updateProperty('date', null)
            ->assertSetStrict('date', null);

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }
}

class ComponentWithPublicCarbonCaster extends Component
{
    public Carbon $date;

    public function render()
    {
        return view('null-view');
    }
}
class ComponentWithNullablePublicCarbonCaster extends Component
{
    public ?Carbon $date = null;

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithNullablePublicCarbonImmutableCaster extends Component
{
    public ?CarbonImmutable $date = null;

    public function render()
    {
        return view('null-view');
    }
}

class ComponentWithNullablePublicDateTimeCaster extends Component
{
    public ?\DateTime $date = null;

    public function render()
    {
        return view('null-view');
    }
}
class ComponentWithNullablePublicDateTimeImmutableCaster extends Component
{
    public ?\DateTimeImmutable $date = null;

    public function render()
    {
        return view('null-view');
    }
}
