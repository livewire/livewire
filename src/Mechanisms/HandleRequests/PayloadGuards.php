<?php

namespace Livewire\Mechanisms\HandleRequests;

use Livewire\Exceptions\PayloadTooLargeException;
use Livewire\Exceptions\TooManyComponentsException;

class PayloadGuards
{
    public static function verify()
    {
        // Check payload size limit...
        $maxSize = config('livewire.payload.max_size');

        if ($maxSize !== null) {
            $contentLength = request()->header('Content-Length', 0);

            if ($contentLength > $maxSize) {
                throw new PayloadTooLargeException($contentLength, $maxSize);
            }
        }

        $requestPayload = request('components');

        if (! is_array($requestPayload) || empty($requestPayload)) {
            abort(404);
        }

        foreach ($requestPayload as $component) {
            if (! is_array($component)
                || ! is_string($component['snapshot'] ?? null)
                || ! is_array($component['updates'] ?? null)
                || ! is_array($component['calls'] ?? null)
            ) {
                abort(404);
            }
        }

        // Check max components limit...
        $maxComponents = config('livewire.payload.max_components');

        if ($maxComponents !== null && count($requestPayload) > $maxComponents) {
            throw new TooManyComponentsException(count($requestPayload), $maxComponents);
        }

        return $requestPayload;
    }
}