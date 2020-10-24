<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class NormalizeComponentPropertiesForJavaScript extends NormalizeDataForJavaScript implements HydrationMiddleware
{
    protected const JAVASCRIPT_MAX_SAFE_INTEGER = 9007199254740991;

    public static function hydrate($instance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        foreach ($instance->getPublicPropertiesDefinedBySubClass() as $key => $value) {

            // The javascript maximum integer is 9007199254740991. 
            // If the value is larger than that, it should be converted to a string
            // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Number/MAX_SAFE_INTEGER
            if (is_numeric($value) && $value > self::JAVASCRIPT_MAX_SAFE_INTEGER) {
                $instance->$key = (string) $value;
            }

            if (is_array($value)) {
                $instance->$key = static::reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
            }

            if ($value instanceof EloquentCollection) {
                // Preserve collection items order by reindexing underlying array.
                $instance->$key = $value->values();
            }
        }
    }
}
