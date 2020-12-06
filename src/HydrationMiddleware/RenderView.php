<?php

namespace Livewire\HydrationMiddleware;

class RenderView implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        $html = $instance->output();

        data_set($response, 'effects.html', $html);

        if ($selector = $instance->getOutputSelector()) {
            data_set($response, 'effects.selector', $selector);
        }
    }
}
