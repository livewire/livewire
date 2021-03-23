<?php

namespace Tests\Browser\ChangeToLivewireClass;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /**
     * Test that Livewire handles the situation when:
     * - a browser loads a page with a Livewire component,
     * - the Component class is subsequently updated
     *   - properties are added and removed,
     *   - properties are in a different order,
     * - the browser triggers a Livewire request that interacts with the new class.
     */
    public function test()
    {
        $this->browse(function ($browser) {

            // use the Component class when $zProp is not set
            $this->prepareComponentClass('ComponentPartA');

            $livewire = Livewire::visit($browser, Component::class)
                ->assertSee('$aProp: A')
                ->assertSee('$zProp: (unset)')
                ->assertSee('$count: 1')
            ;

            // use the Component class when $aProp is not set instead,
            // and the properties are in a different order
            $this->prepareComponentClass('ComponentPartB');

            $livewire->waitForLivewire()
                ->click('@inc-count')
                ->assertSee('$aProp: (unset)')
                ->assertSee('$zProp: Z')
                ->assertSee('$count: 2')
            ;
        });
    }

    /**
     * Create a Component.php file with the desired content.
     *
     * @return void
     */
    private function prepareComponentClass(string $class): void
    {
        $body = file_get_contents(__DIR__ . "/$class.php");
        file_put_contents(
            __DIR__ . "/Component.php",
            str_replace($class, 'Component', $body)
        );
    }
}
