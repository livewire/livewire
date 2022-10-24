<?php

namespace Livewire\Features\SupportUnitTesting;

use PHPUnit\Framework\Assert as PHPUnit;
use Illuminate\Support\Arr;

trait MakesAssertions
{
    public function assertSee($values, $escape = true)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringContainsString(
                $escape ? e($value): $value,
                $this->html()
            );
        }

        return $this;
    }

    public function assertDontSee($values, $escape = true)
    {
        foreach (Arr::wrap($values) as $value) {
            PHPUnit::assertStringNotContainsString(
                $escape ? e($value): $value,
                $this->html()
            );
        }

        return $this;
    }

    public function assertSeeText($value, $escape = true)
    {
        $value = Arr::wrap($value);

        $values = $escape ? array_map('e', ($value)) : $value;

        $content = $this->html();

        tap(strip_tags($content), function ($content) use ($values) {
            foreach ($values as $value) {
                PHPUnit::assertStringContainsString((string) $value, $content);
            }
        });

        return $this;
    }
}
