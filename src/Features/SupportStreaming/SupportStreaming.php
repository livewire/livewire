<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\ComponentHook;
use function Livewire\on;

class SupportStreaming extends ComponentHook
{
    protected static $response;

    public static function provide()
    {
        on('flush-state', function () {
            static::$response = null;
        });
    }

    public function stream($content, $replace = false, $name = null, $el = null, $ref = null)
    {
        static::ensureStreamResponseStarted();

        $hasName = $name !== null;
        $hasEl = $el !== null;
        $hasRef = $ref !== null;

        $type = match (true) {
            $hasName => 'directive',
            $hasEl => 'element',
            $hasRef => 'ref',
        };

        static::streamContent([
            'id' => $this->component->getId(),
            'type' => $type,
            'content' => $content,
            'mode' => $replace ? 'replace' : 'default',
            'name' => $name,
            'el' => $el,
            'ref' => $ref,
        ]);
    }

    public static function ensureStreamResponseStarted()
    {
        if (static::$response) return;

        static::$response = response()->stream(fn () => null , 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
            'X-Livewire-Stream' => true,
        ]);

        static::$response->sendHeaders();
    }

    public static function streamContent($body)
    {
        echo json_encode(['stream' => true, 'body' => $body, 'endStream' => true]);

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
