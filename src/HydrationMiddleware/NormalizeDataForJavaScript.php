<?php

namespace Livewire\HydrationMiddleware;

abstract class NormalizeDataForJavaScript
{
    protected static function reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        // Make sure string keys are last (but not ordered) and numeric keys are ordered.
        // JSON.parse will do this on the frontend, so we'll get ahead of it.

        $itemsWithNumericKeys = array_filter($value, function ($key) {
            return is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);
        ksort($itemsWithNumericKeys);

        $itemsWithStringKeys = array_filter($value, function ($key) {
            return ! is_numeric($key);
        }, ARRAY_FILTER_USE_KEY);

        $normalizedData = array_merge($itemsWithNumericKeys, $itemsWithStringKeys);

        return array_map(function ($value) {
            return static::reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
        }, $normalizedData);
    }
}
