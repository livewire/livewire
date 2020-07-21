<?php

namespace Livewire;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportViewOptimizations
{
    protected $viewHashesById = [];

    static function init()
    {
        return new static;
    }

    function __construct()
    {
        Livewire::listen('component.dehydrate.initial', function ($component, $response) {
            $memo = $response->memo;
            $memo['viewHash'] = hash('crc32b', $response->dom);
            $response->memo = $memo;
        });

        Livewire::listen('component.hydrate.subsequent', function ($component, $request) {
            $this->viewHashesById[$component->id] = $request['memo']['viewHash'];
        });

        Livewire::listen('component.dehydrate.subsequent', function ($component, $response) {
            $oldHash = $this->viewHashesById[$component->id];

            $memo = $response->memo;
            $memo['viewHash'] = $hash = hash('crc32b', $response->dom);
            $response->memo = $memo;

            if ($oldHash === $hash) {
                $response->dom = null;
            }
        });
    }

    function valueIsntAFileResponse($value)
    {
        return ! $value instanceof StreamedResponse
            && ! $value instanceof BinaryFileResponse;
    }

    function captureOutput($callback)
    {
        ob_start();

        $callback();

        return ob_get_clean();
    }
}
