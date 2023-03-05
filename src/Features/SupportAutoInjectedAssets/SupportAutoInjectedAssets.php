<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Livewire\ComponentHook;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use function Livewire\on;

class SupportAutoInjectedAssets extends ComponentHook
{
    static $hasRenderedAComponentThisRequest = false;

    static function provide()
    {
        app('events')->listen(RequestHandled::class, function ($handled) {
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) return;
            if ($handled->response->status() !== 200) return;
            if (! static::$hasRenderedAComponentThisRequest) return;
            if (app(FrontendAssets::class)->hasRenderedScripts) return;

            $html = $handled->response->getContent();

            if (str($html)->contains('</html>')) {
                $handled->response->setContent(static::injectAssets($html));
            }
        });
    }

    public function dehydrate()
    {
        static::$hasRenderedAComponentThisRequest = true;
    }

    static function injectAssets($html)
    {
        $replacement = Blade::render('@livewireScripts').'</html>';
        $html = str($html)->replaceLast('</html>', $replacement);

        return Blade::render('@livewireStyles').$html;
    }
}
