<?php

namespace Livewire\HydrationMiddleware;

class NormalizeServerMemoSansDataForJavaScript extends NormalizeDataForJavaScript implements HydrationMiddleware
{
    public static function hydrate($instance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        foreach ($response->memo as $key => $value) {
            if ($key === 'data') continue;

             if (is_array($value)) {
                $response->memo[$key] = static::reindexArrayWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
            }
        }
    }
}
