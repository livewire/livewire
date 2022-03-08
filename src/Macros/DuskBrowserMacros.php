<?php

namespace Livewire\Macros;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Laravel\Dusk\Browser;
use function Livewire\str;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * @mixin Browser
 */
class DuskBrowserMacros
{
    public function assertAttributeMissing()
    {
        return function ($selector, $attribute) {
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
            PHPUnit::assertEquals($expects, head($this->script(
                str($js)->start('return ')
            )));

            return $this;
        };
    }

    public function runScript()
    {
        return function ($js) {
            $this->script([$js]);

            return $this;
        };
    }

    public function assertClassMissing()
    {
        return function ($selector, $className) {
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
            return $this->waitUsing(5, 75, function () {
                return $this->driver->executeScript("return !! window.Livewire.components.initialRenderIsFinished");
            });
        };
    }

    public function actionTriggersLivewireMessage() {
        return function ($actionCallback) {
            $id = rand(100, 1000);
            $this->script([
                "window.duskIsWaitingForLivewireRequest{$id} = false;",
                "window.Livewire.hook('message.sent', () => { window.duskIsWaitingForLivewireRequest{$id} = true })",
                "window.Livewire.hook('message.processed', () => { delete window.duskIsWaitingForLivewireRequest{$id} })",
                "window.Livewire.hook('message.failed', () => { delete window.duskIsWaitingForLivewireRequest{$id} })",
            ]);

            $actionCallback($this);

            // Wait a quick sec for Livewire to hear a click and send a request.
            $this->pause(25);

            // If our flag is still false, no message was sent...
            if ($this->driver->executeScript("return window.duskIsWaitingForLivewireRequest{$id} === false")) {
                PHPUnit::fail("Expected Livewire request was never sent");
            }

            // Else wait for the flag to disappear
            return $this->waitUsing(5, 50, function () use ($id) {
                return $this->driver->executeScript("return window.duskIsWaitingForLivewireRequest{$id} === undefined");
            }, 'Livewire request never completed');
        };
    }

    public function waitForLivewire()
    {
        return function ($callback = null) {
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

    public function assertLivewireError()
    {
        return function() {
            try {
                $e = $this->driver->findElement(WebDriverBy::id("livewire-error"));
            } catch (NoSuchElementException $e) {
                $e = null;
            }
            PHPUnit::assertNotNull($e, "Livewire error dialog not detected.");
        };
    }

    public function assertNoLivewireError()
    {
        return function() {
            try {
                $e = $this->driver->findElement(WebDriverBy::id("livewire-error"));
            } catch (NoSuchElementException $e) {
                $e = null;
            }
            PHPUnit::assertNull($e, "Livewire error dialog detected.");
        };
    }

    public function online()
    {
        return function () {
            return tap($this)->script("window.dispatchEvent(new Event('online'))");
        };
    }

    public function offline()
    {
        return function () {
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
        };
    }
}
