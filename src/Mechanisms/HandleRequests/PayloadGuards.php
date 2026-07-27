<?php

namespace Livewire\Mechanisms\HandleRequests;

use Livewire\Exceptions\PayloadTooLargeException;
use Livewire\Exceptions\TooManyCallsException;
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

    public static function verifyPayloadMaxCalls($calls)
    {
        $maxCalls = config('livewire.payload.max_calls');

        if ($maxCalls !== null && count($calls) > $maxCalls) {
            throw new TooManyCallsException(count($calls), $maxCalls);
        }
    }

    public static function verifySnapshotStructure($snapshot)
    {
        if (! is_array($snapshot)
            || ! is_array($snapshot['data'] ?? null)
            || ! is_array($snapshot['memo'] ?? null)
            || ! is_string($snapshot['checksum'] ?? null)
            || ! is_string($snapshot['memo']['id'] ?? null)
            || ! is_string($snapshot['memo']['name'] ?? null)
        ) {
            if (config('app.debug')) throw new \InvalidArgumentException('Invalid Livewire snapshot structure: expected [data], [memo], [checksum], [memo.id], and [memo.name].');

            abort(404);
        }
    }

    public static function verifyCallsStructure($calls)
    {
        foreach ($calls as $call) {
            if (! is_array($call)
                || ! is_string($call['method'] ?? null)
                || ! is_array($call['params'] ?? null)
            ) {
                if (config('app.debug')) throw new \InvalidArgumentException('Invalid Livewire call structure: each call must contain [method] (string) and [params] (array).');

                abort(404);
            }
        }
    }
}