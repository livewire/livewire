<?php

namespace Livewire\Features\SupportStreaming;

use Livewire\ComponentHook;

class SupportStreaming extends ComponentHook
{
    protected static $response;

    public function stream($name, $content, $replace = false)
    {
        static::ensureStreamResponseStarted();

        static::streamContent(['name' => $name, 'content' => $content, 'replace' => $replace]);
    }

    public static function ensureStreamResponseStarted()
    {
        if (static::$response) return;

        static::$response = response()->stream(null , 200, [
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
