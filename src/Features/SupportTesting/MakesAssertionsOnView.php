<?php

namespace Livewire\Features\SupportTesting;

use Illuminate\View\View;
use PHPUnit\Framework\Assert as PHPUnit;

trait MakesAssertionsOnView
{
    /**
     * @param string $propertyName The name of the property on the Livewire component that is expected to be bound in the view/
     * @param int|null $times If null, then the property is checked to be bound at least once. Else, exactly that amount.
     * @return Testable|MakesAssertionsOnView
     */
    public function assertPropertyBound(string $propertyName, int $times = null): self
    {
        $matchCount = preg_match_all('/wire:model[^>]+"' . preg_quote($propertyName) . '"/msi', $this->html());
        if (!is_int($matchCount)) $matchCount = 0;

        PHPUnit::assertThat(
            $matchCount,
            $times === null ? PHPUnit::greaterThan(0) : PHPUnit::equalTo($times),
            $this->boundAmountFailMessage('Property', $propertyName, $times));

        return $this;
    }

    /**
     * @param string $propertyName
     * @return Testable|MakesAssertionsOnView
     */
    public function assertPropertyNotBound(string $propertyName): self
    {
        $this->assertPropertyBound($propertyName, 0);
        return $this;
    }

    /**
     * @param string $actionName The action / method on the Livewire component that is expected to be bound in the view.
     * @param string|null $eventName The dispatched browser event the action must trigger on. For example: click, keydown or submit.
     * @param int|null $times If null, then the action is checked to be bound at least once. Else, exactly that amount.
     * @return Testable|MakesAssertionsOnView
     */
    public function assertActionBound(string $actionName, string $eventName = null, int $times = null): self
    {
        $regex = '/wire:(?!model)[^>]+"' . preg_quote($actionName) . '"/msi';
        if ($eventName) {
            $regex = '/wire:' . preg_quote($eventName) . '[^>]+"' . preg_quote($actionName) . '"/msi';
        }

        $matchCount = preg_match_all($regex, $this->html());

        if (!is_int($matchCount)) $matchCount = 0;

        PHPUnit::assertThat(
            $matchCount,
            $times === null ? PHPUnit::greaterThan(0) : PHPUnit::equalTo($times),
            $this->boundAmountFailMessage('Action', $actionName, $times));

        return $this;
    }

    /**
     * @param string $key
     * @return Testable|MakesAssertionsOnView
     */
    public function assertActionNotBound(string $key): self
    {
        $this->assertPropertyBound($key, 0);
        return $this;
    }

    private function boundAmountFailMessage($type, $subjectName, ?int $times): string
    {
        if ($times === 0) {
            $failMessage = '%s "%s" must not be bound';
        } elseif ($times === null) {
            $failMessage = '%s "%s" was not bound at least once';
        } else {
            $failMessage = '%s "%s" was not bound exactly %d times';
        }

        return sprintf($failMessage, $type, $subjectName, $times);
    }
}
