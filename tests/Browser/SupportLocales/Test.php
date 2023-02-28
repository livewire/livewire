<?php

namespace Tests\Browser\SupportLocales;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function it_functions_without_locale_prefix_in_url()
    {
        /**
         * We can't use `Livewire::visit()` as we need the URL to run
         * some tests against.
         */
        $url = '/livewire-dusk/'.urlencode(Component::class);

        $this->browse(function ($browser) use ($url) {
            $browser->visit($url)->waitForLivewireToLoad()
                ->assertPathIs($url)
                ->assertSeeIn('@locale', 'en')
                ->assertSeeIn('@count', 0)

                ->waitForLivewire()->click('@increaseCount')

                ->assertPathIs($url)
                ->assertSeeIn('@locale', 'en')
                ->assertSeeIn('@count', 1)
            ;
        });
    }

    /** @test */
    public function it_functions_with_locale_prefix_in_url()
    {
        /**
         * We can't use `Livewire::visit()` as we need to prefix the locale
         * to the URL to test localisation is being applied correctly.
         */
        $locale = 'de';
        $url = '/' . $locale . '/livewire-dusk/' . urlencode(Component::class);

        $this->browse(function ($browser) use ($url) {
            $browser->visit($url)->waitForLivewireToLoad()

                ->assertPathIs($url)
                ->assertSeeIn('@locale', 'de')
                ->assertSeeIn('@count', 0)

                ->waitForLivewire()->click('@increaseCount')

                ->assertPathIs($url)
                ->assertSeeIn('@locale', 'de')
                ->assertSeeIn('@count', 1)
            ;
        });
    }

    /** @test */
    public function it_uses_app_default_if_locale_has_been_tampered_with_and_is_not_a_valid_locale()
    {
        /**
         * We can't use `Livewire::visit()` as we need to prefix the locale
         * to the URL to test localisation is being applied correctly.
         */
        $locale = 'random';
        $url = '/' . $locale . '/livewire-dusk/' . urlencode(Component::class);

        $this->browse(function ($browser) use ($url) {
            $browser->visit($url)->waitForLivewireToLoad()

                ->assertPathIs($url)
                ->assertSeeIn('@locale', 'en')
                ->assertSeeIn('@count', 0)

                ->waitForLivewire()->click('@increaseCount')

                ->assertPathIs($url)
                ->assertSeeIn('@locale', 'en')
                ->assertSeeIn('@count', 1)
            ;
        });
    }
}
