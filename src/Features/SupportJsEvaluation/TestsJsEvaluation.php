<?php

namespace Livewire\Features\SupportJsEvaluation;

use PHPUnit\Framework\Assert;

trait TestsJsEvaluation
{
    public function assertJs(string $expression, ...$params)
    {
        $js = $this->effects['xjs'] ?? [];

        $expression = preg_replace('/\n\s*/', '', $expression);

        Assert::assertTrue(
            collect($js)
                ->map(fn ($item) => [
                    'expression' => preg_replace('/\n\s*/', '', $item['expression']),
                    'params' => $item['params'] ?? [],
                ])
                ->contains(function ($item) use ($expression, $params) {
                    return $item['expression'] === $expression && $item['params'] == $params;
                }),
            'Failed asserting that dispatched JS matches expected JS.'
        );

        return $this;
    }
}
