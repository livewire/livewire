<?php

namespace LegacyTests\Browser\SupportDateTimes;

use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class Test extends TestCase
{
    public function test_date_support(): void
    {
        Livewire::test(new class extends Component {
            public $native;
            public $nativeImmutable;
            public $carbon;
            public $carbonImmutable;
            public $illuminate;

            public function mount()
            {
                $this->native = new DateTime('01/01/2001');
                $this->nativeImmutable = new DateTimeImmutable('01/01/2001');
                $this->carbon = \Carbon\Carbon::parse('01/01/2001');
                $this->carbonImmutable = CarbonImmutable::parse('01/01/2001');
                $this->illuminate = Carbon::parse('01/01/2001');
            }

            public function addDay()
            {
                $this->native->modify('+1 day');
                $this->nativeImmutable = $this->nativeImmutable->modify('+1 day');
                $this->carbon->addDay(1);
                $this->carbonImmutable = $this->carbonImmutable->addDay(1);
                $this->illuminate->addDay(1);
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        <span>native-{{ $native->format('m/d/Y') }}</span>
                        <span>nativeImmutable-{{ $nativeImmutable->format('m/d/Y') }}</span>
                        <span>carbon-{{ $carbon->format('m/d/Y') }}</span>
                        <span>carbonImmutable-{{ $carbonImmutable->format('m/d/Y') }}</span>
                        <span>illuminate-{{ $illuminate->format('m/d/Y') }}</span>
                    </div>
                HTML;
            }
        })
            ->assertSee('native-01/01/2001')
            ->assertSee('nativeImmutable-01/01/2001')
            ->assertSee('carbon-01/01/2001')
            ->assertSee('carbonImmutable-01/01/2001')
            ->assertSee('illuminate-01/01/2001')
            ->call('addDay')
            ->assertSee('native-01/02/2001')
            ->assertSee('nativeImmutable-01/02/2001')
            ->assertSee('carbon-01/02/2001')
            ->assertSee('carbonImmutable-01/02/2001')
            ->assertSee('illuminate-01/02/2001');
    }
}
