<?php

namespace Livewire\BootTest;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\Wireable;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function test_component_booth_method_is_called_before_running_synths()
    {
        Livewire::visit(new class extends Component {

            public SomeObject $someObject;

            public function boot()
            {
                SomeOtherObjectWithStaticProperty::$someValue = 'valueSetByComponentBoot';
            }

            public function mount()
            {
                $this->someObject = new SomeObject('test');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="value">{{ $someObject->bar }}</div>
                    <button dusk="button" wire:click="$refresh">Click</button>
                </div>
                HTML;
            }
        })
            ->waitForLivewire()
            ->click('@button')
            ->waitForLivewire()
            ->assertSeeIn('@value', 'valueSetByComponentBoot')
            ->pause(5000)
            ;
    }
}

class SomeObject implements Wireable {
    public function __construct(public $foo, public $bar = null)
    {
    }

    public function toLivewire()
    {
        return [
            'foo' => $this->foo,
            'bar' => $this->bar,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static($value['foo'], SomeOtherObjectWithStaticProperty::$someValue);
    }
}

class SomeOtherObjectWithStaticProperty {
    public static $someValue = 'defaultValue';
}
