<?php

namespace Livewire\Features;

use Illuminate\Contracts\Support\Responsable;
use Livewire\Livewire;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SupportFileDownloads
{
    static function init() { return new static; }

    protected $downloadsById = [];

    function __construct()
    {
        Livewire::listen('action.returned', function ($component, $action, $returned) {
            if($returned instanceof Responsable){
                $returned = $returned->toResponse(request());
            }

            if ($this->valueIsntAFileResponse($returned)) return;

            $response = $returned;

            $name = $this->getFilenameFromContentDispositionHeader(
                $response->headers->get('Content-Disposition')
            );

            $binary = $this->captureOutput(function () use ($response) {
                $response->sendContent();
            });

            $content = base64_encode($binary);

            $this->downloadsById[$component->id] = [
                'name' => $name,
                'content' => $content,
                'contentType' => $response->headers->get('Content-Type'),
            ];
        });

        Livewire::listen('component.dehydrate.subsequent', function ($component, $response) {
            if (! $download = $this->downloadsById[$component->id] ?? false) return;

            $response->effects['download'] = $download;
        });

        Livewire::listen('flush-state', function() {
            $this->downloadsById = [];
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

    function getFilenameFromContentDispositionHeader($header)
    {
        /**
         * The following conditionals are here to allow for quoted and
         * non quoted filenames in the Content-Disposition header.
         *
         * Both of these values should return the correct filename without quotes.
         *
         * Content-Disposition: attachment; filename=filename.jpg
         * Content-Disposition: attachment; filename="test file.jpg"
         */

        if (preg_match('/.*?filename="(.+?)"/', $header, $matches)) {
            return $matches[1];
        }

        if (preg_match('/.*?filename=([^; ]+)/', $header, $matches)) {
            return $matches[1];
        }

        return 'download';
    }
}
