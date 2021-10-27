<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class NormalizeComponentPropertiesForJavaScript extends NormalizeDataForJavaScript implements HydrationMiddleware
{
    public static function hydrate($instance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        foreach ($instance->getPublicPropertiesDefinedBySubClass() as $key => $value) {
            if (is_array($value) || $value instanceof Collection) {
                $instance->$key = static::reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
            }

            if ($value instanceof EloquentCollection) {
                // Preserve collection items order by reindexing underlying array.
                $instance->$key = $value->values();
            }
        }
    }
}
