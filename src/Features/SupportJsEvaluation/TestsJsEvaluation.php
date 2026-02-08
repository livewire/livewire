<?php

namespace Livewire\Features\SupportJsEvaluation;

use PHPUnit\Framework\Assert;

trait TestsJsEvaluation
{
    public function assertJs(string $expression, ...$params)
    {
        $js = $this->effects['xjs'] ?? [];

        Assert::assertTrue(
            collect($js)
                ->contains(function ($item) use ($expression, $params) {
                    return $item['expression'] === $expression
                        && ($item['params'] ?? []) == $params;
                }),
            "Failed asserting that JS [{$expression}] was evaluated."
        );

        return $this;
    }

    public function assertNoJs()
    {
        $js = $this->effects['xjs'] ?? [];

        Assert::assertEmpty(
            $js,
            'Failed asserting that no JS was evaluated.'
        );

        return $this;
    }
}
