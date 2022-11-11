<?php

namespace Livewire\Features\SupportFileDownloads;

use function Livewire\store;
use function Livewire\on;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Illuminate\Contracts\Support\Responsable;
use Livewire\Mechanisms\DataStore;

class SupportFileDownloads
{
    // static function init() { return new static; }

    // protected $downloadsById = [];

    function boot()
    {
        on('call', function ($synth, $target, $method, $params, $addEffect) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($returned) use ($target) {
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

                store($target)->set('download', [
                    'name' => $name,
                    'content' => $content,
                    'contentType' => $response->headers->get('Content-Type'),
                ]);
            };
        });

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof \Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth) return;

            if (! $download = store($target)->get('download')) return;

            $context->addEffect('download', $download);
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
