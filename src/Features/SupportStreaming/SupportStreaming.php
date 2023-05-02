<?php

namespace Livewire\Features\SupportStreaming;

use Illuminate\Support\Facades\Artisan;
use Livewire\ComponentHook;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class SupportStreaming extends ComponentHook
{
    protected static $response;

    public function stream($name, $content, $append = false)
    {
        static::ensureStreamResponseStarted();

        static::streamContent(['name' => $name, 'content' => $content, 'append' => $append]);
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
        echo json_encode(['stream' => true, 'body' => $body]);
        ob_flush();
        flush();
    }
}
