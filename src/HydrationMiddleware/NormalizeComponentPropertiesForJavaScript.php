<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class NormalizeComponentPropertiesForJavaScript extends NormalizeDataForJavaScript implements HydrationMiddleware
{
    public static function hydrate($instance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        foreach ($instance->getPublicPropertiesDefinedBySubClass() as $key => $value) {
            // If the value is larger than the javascript integer maximum, it should be converted to a string
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
