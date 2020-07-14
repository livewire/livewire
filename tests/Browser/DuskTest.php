<?php

namespace Tests\Browser;

use Illuminate\Support\Facades\Route;

class DuskTest extends TestCase
{
    /** @test */
    public function something()
    {
        $this->tweakApplication(function () {
            Route::get('/hey', function () {
                return ['hey' => 'there'];
            });
        });

        $this->browse(function ($browser) {
            $browser->visit('/hey')->assertSee('hey');
        });

        $this->removeApplicationTweaks();
    }
}
