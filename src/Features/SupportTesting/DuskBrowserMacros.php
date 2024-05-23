<?php

namespace Livewire\Features\SupportTesting;

use function Livewire\str;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert as PHPUnit;

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

    public function assertNotInViewPort()
    {
        return function ($selector) {
            /** @var \Laravel\Dusk\Browser $this */
            return $this->assertInViewPort($selector, invert: true);
        };
    }

    public function assertInViewPort()
    {
        return function ($selector, $invert = false) {
            /** @var \Laravel\Dusk\Browser $this */

            $fullSelector = $this->resolver->format($selector);

            $result = $this->script(
                'const rect = document.querySelector(\''.$fullSelector.'\').getBoundingClientRect();
                 return (
                     rect.top >= 0 &&
                     rect.left >= 0 &&
                     rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                     rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                 );',
                 $selector
            )[0];

            PHPUnit::assertEquals($invert ? false : true, $result);

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
            return $this->waitUsing(6, 25, function () {
                return $this->driver->executeScript('return !! window.Livewire.initialRenderIsFinished');
            });
        };
    }

    public function waitForLivewire()
    {
        return function ($callback = null) {
            /** @var \Laravel\Dusk\Browser $this */
            $id = str()->random();

            $this->script([
                "window.duskIsWaitingForLivewireRequest{$id} = true",
                "window.Livewire.hook('request', ({ respond, succeed, fail }) => {
                    window.duskIsWaitingForLivewireRequest{$id} = true

                    let handle = () => {
                        queueMicrotask(() => {
                            console.log('test')
                            delete window.duskIsWaitingForLivewireRequest{$id}
                        })
                    }

                    succeed(handle)
                    fail(handle)
                })",
            ]);

            if ($callback) {
                $callback($this);

                return $this->waitUsing(6, 25, function () use ($id) {
                    return $this->driver->executeScript("return window.duskIsWaitingForLivewireRequest{$id} === undefined");
                }, 'Livewire request was never triggered');
            }

            // If no callback is passed, make ->waitForLivewire a higher-order method.
            return new class($this, $id) {
                protected $browser;
                protected $id;

                public function __construct($browser, $id) { $this->browser = $browser; $this->id = $id; }

                public function __call($method, $params)
                {
                    return tap($this->browser->{$method}(...$params), function ($browser) {
                        $browser->waitUsing(6, 25, function () use ($browser) {
                            return $browser->driver->executeScript("return window.duskIsWaitingForLivewireRequest{$this->id} === undefined");
                        }, 'Livewire request was never triggered');
                    });
                }
            };
        };
    }

    public function waitForNoLivewire()
    {
        return function ($callback = null) {
            /** @var \Laravel\Dusk\Browser $this */
            $id = str()->random();

            $this->script([
                "window.duskIsWaitingForLivewireRequest{$id} = true",
                "window.Livewire.hook('request', ({ respond, succeed, fail }) => {
                    window.duskIsWaitingForLivewireRequest{$id} = true

                    let handle = () => {
                        queueMicrotask(() => {
                            delete window.duskIsWaitingForLivewireRequest{$id}
                        })
                    }

                    succeed(handle)
                    fail(handle)
                })",
            ]);

            if ($callback) {
                $callback($this);

                return $this->waitUsing(6, 25, function () use ($id) {
                    return $this->driver->executeScript("return window.duskIsWaitingForLivewireRequest{$id}");
                }, 'Livewire request was triggered');
            }

            // If no callback is passed, make ->waitForNoLivewire a higher-order method.
            return new class($this, $id) {
                protected $browser;
                protected $id;

                public function __construct($browser, $id) { $this->browser = $browser; $this->id = $id; }

                public function __call($method, $params)
                {
                    return tap($this->browser->{$method}(...$params), function ($browser) {
                        $browser->waitUsing(6, 25, function () use ($browser) {
                            return $browser->driver->executeScript("return window.duskIsWaitingForLivewireRequest{$this->id}");
                        }, 'Livewire request was triggered');
                    });
                }
            };
        };
    }

    public function waitForNavigate()
    {
        return function ($callback = null) {
            /** @var \Laravel\Dusk\Browser $this */
            $id = str()->random();

            $this->script([
                "window.duskIsWaitingForLivewireNavigate{$id} = true",
                "window.handler{$id} = () => {
                    window.duskIsWaitingForLivewireNavigate{$id} = true

                    document.removeEventListener('livewire:navigated', window.handler{$id})

                    queueMicrotask(() => {
                        delete window.duskIsWaitingForLivewireNavigate{$id}
                    })
                }",
                "document.addEventListener('livewire:navigated', window.handler{$id})",
            ]);

            if ($callback) {
                $callback($this);

                return $this->waitUsing(6, 25, function () use ($id) {
                    return $this->driver->executeScript("return window.duskIsWaitingForLivewireNavigate{$id} === undefined");
                }, 'Livewire navigate was never triggered');
            }

            // If no callback is passed, make ->waitForNavigate a higher-order method.
            return new class($this, $id) {
                protected $browser;
                protected $id;
                public function __construct($browser, $id) { $this->browser = $browser; $this->id = $id; }

                public function __call($method, $params)
                {
                    return tap($this->browser->{$method}(...$params), function ($browser) {
                        $browser->waitUsing(6, 25, function () use ($browser) {
                            return $browser->driver->executeScript("return window.duskIsWaitingForLivewireNavigate{$this->id} === undefined");
                        }, 'Livewire navigate was never triggered');
                    });
                }
            };
        };
    }

    public function waitForNavigateRequest()
    {
        return function ($callback = null) {
            /** @var \Laravel\Dusk\Browser $this */
            $id = str()->random();

            $this->script([
                "window.duskIsWaitingForLivewireNavigateRequestStarted{$id} = false",
                "window.duskIsWaitingForLivewireNavigateRequestFinished{$id} = true",
                'let cleanupRequest = () => {}',
                "cleanupRequest = Livewire.hook('navigate.request', () => {
                    window.duskIsWaitingForLivewireNavigateRequestStarted{$id} = true

                    cleanupRequest()
                })",
                "window.handler{$id} = () => {
                    if (! window.duskIsWaitingForLivewireNavigateRequestStarted{$id}) {
                        return
                    }

                    window.duskIsWaitingForLivewireNavigateRequestFinished{$id} = true

                    document.removeEventListener('livewire:navigated', window.handler{$id})

                    queueMicrotask(() => {
                        delete window.duskIsWaitingForLivewireNavigateRequestStarted{$id}
                        delete window.duskIsWaitingForLivewireNavigateRequestFinished{$id}
                    })
                }",
                "document.addEventListener('livewire:navigated', window.handler{$id})",
            ]);

            if ($callback) {
                $callback($this);

                return $this->waitUsing(6, 25, function () use ($id) {
                    return $this->driver->executeScript("return window.duskIsWaitingForLivewireNavigateRequestFinished{$id} === undefined");
                }, 'Livewire navigate request was never completed');
            }

            // If no callback is passed, make ->waitForNavigate a higher-order method.
            return new class($this, $id)
            {
                protected $browser;
                protected $id;

                public function __construct($browser, $id)
                {
                    $this->browser = $browser;
                    $this->id = $id;
                }

                public function __call($method, $params)
                {
                    return tap($this->browser->{$method}(...$params), function ($browser) {
                        $browser->waitUsing(6, 25, function () use ($browser) {
                            return $browser->driver->executeScript("return window.duskIsWaitingForLivewireNavigateRequestFinished{$this->id} === undefined");
                        }, 'Livewire navigate request was never completed');
                    });
                }
            };
        };
    }

    public function waitForNoNavigateRequest()
    {
        return function ($callback = null) {
            /** @var \Laravel\Dusk\Browser $this */
            $id = str()->random();

            $this->script([
                "window.duskIsWaitingForLivewireNavigateRequestStarted{$id} = true",
                'let cleanupRequest = () => {}',
                "cleanupRequest = Livewire.hook('navigate.request', () => {
                    window.duskIsWaitingForLivewireNavigateRequestStarted{$id} = true

                    cleanupRequest()

                    queueMicrotask(() => {
                        delete window.duskIsWaitingForLivewireNavigateRequestStarted{$id}
                    })
                })",
            ]);

            if ($callback) {
                $callback($this);

                return $this->waitUsing(6, 25, function () use ($id) {
                    return $this->driver->executeScript("return window.duskIsWaitingForLivewireNavigateRequestStarted{$id}");
                }, 'Livewire navigate request was never completed');
            }

            // If no callback is passed, make ->waitForNavigate a higher-order method.
            return new class($this, $id)
            {
                protected $browser;
                protected $id;

                public function __construct($browser, $id)
                {
                    $this->browser = $browser;
                    $this->id = $id;
                }

                public function __call($method, $params)
                {
                    return tap($this->browser->{$method}(...$params), function ($browser) {
                        $browser->waitUsing(6, 25, function () use ($browser) {
                            return $browser->driver->executeScript("return window.duskIsWaitingForLivewireNavigateRequestStarted{$this->id}");
                        }, 'Livewire navigate request was completed');
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

    public function selectMultiple()
    {
        return function ($field, $values = []) {
            $element = $this->resolver->resolveForSelection($field);

            $options = $element->findElements(WebDriverBy::tagName('option'));

            if (empty($values)) {
                $maxSelectValues = sizeof($options) - 1;
                $minSelectValues = rand(0, $maxSelectValues);
                foreach (range($minSelectValues, $maxSelectValues) as $optValue) {
                    $options[$optValue]->click();
                }
            } else {
                foreach ($options as $option) {
                    $optValue = (string)$option->getAttribute('value');
                    if (in_array($optValue, $values)) {
                        $option->click();
                    }
                }
            }

            return $this;
        };
    }

    public function assertConsoleLogHasWarning()
    {
        return function($expectedMessage){
            $logs = $this->driver->manage()->getLog('browser');

            $containsError = false;

            foreach ($logs as $log) {
                if (! isset($log['message']) || ! isset($log['level']) || $log['level'] !== 'WARNING') continue;


                if(str($log['message'])->contains($expectedMessage)) {
                    $containsError = true;
                }
            }

            PHPUnit::assertTrue($containsError, "Console log error message \"{$expectedMessage}\" not found");

            return $this;
        };
    }

    public function assertConsoleLogMissingWarning()
    {
        return function($expectedMessage){
            $logs = $this->driver->manage()->getLog('browser');

            $containsError = false;

            foreach ($logs as $log) {
                if (! isset($log['message']) || ! isset($log['level']) || $log['level'] !== 'WARNING') continue;


                if(str($log['message'])->contains($expectedMessage)) {
                    $containsError = true;
                }
            }

            PHPUnit::assertFalse($containsError, "Console log error message \"{$expectedMessage}\" was found");

            return $this;
        };
    }
}
