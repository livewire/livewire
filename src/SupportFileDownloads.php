<?php

namespace Livewire;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportFileDownloads
{
    protected $downloadsById = [];

    static function init()
    {
        return new static;
    }

    function __construct()
    {
        Livewire::listen('action.returned', function ($component, $action, $returned) {
            if ($this->valueIsntAStreamedResponse($returned)) return;

            $response = $returned;

            $name = Str::after($response->headers->get('Content-Disposition'), 'filename=');

            $binary = $this->captureOutput(function () use ($response) {
                $response->sendContent();
            });

            $content = base64_encode($binary);

            $this->downloadsById[$component->id] = [
                'name' => $name,
                'content' => $content,
            ];

            $component->skipRender();
        });

        Livewire::listen('component.dehydrate', function ($component, $response) {
            if (! $download = $this->downloadsById[$component->id] ?? false) return;

            $response->download = $download;
        });
    }

    function valueIsntAStreamedResponse($value)
    {
        return ! $value instanceof StreamedResponse;
    }

    function captureOutput($callback)
    {
        ob_start();

        $callback();

        return ob_get_clean();
    }
}
