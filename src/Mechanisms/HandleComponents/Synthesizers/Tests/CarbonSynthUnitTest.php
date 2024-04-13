<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class CarbonSynthUnitTest extends \Tests\TestCase
{
    #[Test]
    public function public_carbon_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithPublicCarbonCaster::class)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', Carbon::parse('2024-02-14'));

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    #[Test]
    public function public_nullable_carbon_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicCarbonCaster::class)
            ->assertSet('date', null, true)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', Carbon::parse('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSet('date', null, true)
            ->updateProperty('date', null)
            ->assertSet('date', null, true);

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    #[Test]
    public function public_carbon_immutable_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicCarbonImmutableCaster::class)
            ->assertSet('date', null, true)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', CarbonImmutable::parse('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSet('date', null, true)
            ->updateProperty('date', null)
            ->assertSet('date', null, true);

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    #[Test]
    public function public_datetime_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicDateTimeCaster::class)
            ->assertSet('date', null, true)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', new \DateTime('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSet('date', null, true)
            ->updateProperty('date', null)
            ->assertSet('date', null, true);

        $this->expectException(\Exception::class);
        $testable->updateProperty('date', 'Bad Date');
    }

    #[Test]
    public function public_datetime_immutable_properties_can_be_cast()
    {
        $testable = Livewire::test(ComponentWithNullablePublicDateTimeImmutableCaster::class)
            ->assertSet('date', null, true)
            ->updateProperty('date', '2024-02-14')
            ->assertSet('date', new \DateTimeImmutable('2024-02-14'))
            ->updateProperty('date', '')
            ->assertSet('date', null, true)
            ->updateProperty('date', null)
            ->assertSet('date', null, true);

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
