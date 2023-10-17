<?php

namespace Livewire\Features\SupportFileDownloads;

use Illuminate\Contracts\Support\Responsable;
use Livewire\ComponentHook;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportFileDownloads extends ComponentHook
{
    public function call()
    {
        return function ($return) {
            if ($return instanceof Responsable) {
                $return = $return->toResponse(request());
            }

            if ($this->valueIsntAFileResponse($return)) {
                return;
            }

            $response = $return;

            $name = $this->getFilenameFromContentDispositionHeader(
                $response->headers->get('Content-Disposition')
            );

            $binary = $this->captureOutput(function () use ($response) {
                $response->sendContent();
            });

            $content = base64_encode($binary);

            $this->storeSet('download', [
                'name' => $name,
                'content' => $content,
                'contentType' => $response->headers->get('Content-Type'),
            ]);
        };
    }

    public function dehydrate($context)
    {
        if (! $download = $this->storeGet('download')) {
            return;
        }

        $context->addEffect('download', $download);
    }

    public function valueIsntAFileResponse($value)
    {
        return ! $value instanceof StreamedResponse
            && ! $value instanceof BinaryFileResponse;
    }

    public function captureOutput($callback)
    {
        ob_start();

        $callback();

        return ob_get_clean();
    }

    public function getFilenameFromContentDispositionHeader($header)
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

        // Support foreign-language filenames (japanese, greek, etc..)...
        if (preg_match('/filename\*=utf-8\'\'(.+)$/i', $header, $matches)) {
            return rawurldecode($matches[1]);
        }

        if (preg_match('/.*?filename="(.+?)"/', $header, $matches)) {
            return $matches[1];
        }

        if (preg_match('/.*?filename=([^; ]+)/', $header, $matches)) {
            return $matches[1];
        }

        return 'download';
    }
}
