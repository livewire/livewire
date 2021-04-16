<?php

namespace Livewire\Macros;

use PHPUnit\Framework\Assert as PHPUnit;
use function Livewire\str;

class DuskBrowserMacros
{
    public function assertAttributeMissing()
    {
        return function ($selector, $attribute) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            $actual = $this->resolver->findOrFail($selector)->getAttribute($attribute);

            PHPUnit::assertNull(
                $actual,
                "Did not see expected attribute [{$attribute}] within element [{$fullSelector}]."
            );

            return $this;
        };
    }

    public function assertNotVisible()
    {
        return function ($selector) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            PHPUnit::assertFalse(
                $this->resolver->findOrFail($selector)->isDisplayed(),
                "Element [{$fullSelector}] is visible."
            );

            return $this;
        };
    }

    public function assertNotPresent()
    {
        return function ($selector) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            PHPUnit::assertTrue(
                is_null($this->resolver->find($selector)),
                "Element [{$fullSelector}] is present."
            );

            return $this;
        };
    }

    public function assertHasClass()
    {
        return function ($selector, $className) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            PHPUnit::assertContains(
                $className,
                explode(' ', $this->attribute($selector, 'class')),
                "Element [{$fullSelector}] missing class [{$className}]."
            );

            return $this;
        };
    }

    public function assertScript()
    {
        return function ($js, $expects = true) {
            /** @var \Laravel\Dusk\Browser $this */
            PHPUnit::assertEquals($expects, head($this->script(
                str($js)->start('return ')
            )));

            return $this;
        };
    }

    public function runScript()
    {
        return function ($js) {
            /** @var \Laravel\Dusk\Browser $this */
            $this->script([$js]);

            return $this;
        };
    }

    public function scrollTo()
    {
        return function ($selector) {
            $this->browser->scrollTo($selector);
            return $this;
        };
    }

    public function assertClassMissing()
    {
        return function ($selector, $className) {
            /** @var \Laravel\Dusk\Browser $this */
            $fullSelector = $this->resolver->format($selector);

            PHPUnit::assertNotContains(
                $className,
                explode(' ', $this->attribute($selector, 'class')),
                "Element [{$fullSelector}] has class [{$className}]."
            );

            return $this;
        };
    }

    public function waitForLivewireToLoad()
    {
        return function () {
            /** @var \Laravel\Dusk\Browser $this */
            return $this->waitUsing(5, 75, function () {
                return $this->driver->executeScript("return !! window.Livewire.components.initialRenderIsFinished");
            });
        };
    }

    public function waitForLivewire()
    {
        return function ($callback = null) {
            /** @var \Laravel\Dusk\Browser $this */
            $id = rand(100, 1000);

            $this->script([
                "window.duskIsWaitingForLivewireRequest{$id} = true",
                "window.Livewire.hook('message.sent', () => { window.duskIsWaitingForLivewireRequest{$id} = true })",
                "window.Livewire.hook('message.processed', () => { delete window.duskIsWaitingForLivewireRequest{$id} })",
                "window.Livewire.hook('message.failed', () => { delete window.duskIsWaitingForLivewireRequest{$id} })",
            ]);

            if ($callback) {
                $callback($this);

                // Wait a quick sec for Livewire to hear a click and send a request.
                $this->pause(25);

                return $this->waitUsing(5, 50, function () use ($id) {
                    return $this->driver->executeScript("return window.duskIsWaitingForLivewireRequest{$id} === undefined");
                }, 'Livewire request was never triggered');
            }

            // If no callback is passed, make ->waitForLivewire a higher-order method.
            return new class($this, $id) {
                public function __construct($browser, $id) { $this->browser = $browser; $this->id = $id; }

                public function __call($method, $params)
                {
                    return tap($this->browser->{$method}(...$params), function ($browser) {
                        $browser->waitUsing(5, 25, function () use ($browser) {
                            return $browser->driver->executeScript("return window.duskIsWaitingForLivewireRequest{$this->id} === undefined");
                        }, 'Livewire request was never triggered');
                    });
                }
            };
        };
    }

    public function online()
    {
        return function () {
            /** @var \Laravel\Dusk\Browser $this */
            return tap($this)->script("window.dispatchEvent(new Event('online'))");
        };
    }

    public function offline()
    {
        return function () {
            /** @var \Laravel\Dusk\Browser $this */
            return tap($this)->script("window.dispatchEvent(new Event('offline'))");
        };
    }
}
