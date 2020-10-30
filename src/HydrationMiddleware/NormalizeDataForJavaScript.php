<?php

namespace Livewire\HydrationMiddleware;

abstract class NormalizeDataForJavaScript
{
    // The javascript maximum integer is 9007199254740991, or 2^53.
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/MAX_SAFE_INTEGER
    protected const JAVASCRIPT_MAX_SAFE_INTEGER = 9007199254740991;

    protected static function reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value)
    {
        if (! is_array($value)) {
            if (is_numeric($value) && $value > self::JAVASCRIPT_MAX_SAFE_INTEGER) {
               return (string) $value;
            }

            return $value;
        }

        $normalizedData = $value;

        // Make sure string keys are last (but not ordered) and numeric keys are ordered.
        // JSON.parse will do this on the frontend, so we'll get ahead of it.
        uksort($normalizedData, function ($a, $b) {
            if (is_numeric($a) && is_numeric($b)) return $a > $b;

            if (! is_numeric($a) && ! is_numeric($b)) return 0;

            if (! is_numeric($a)) return 1;
        });

        return array_map(function ($value) {
            return static::reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
        }, $normalizedData);
    }
}
