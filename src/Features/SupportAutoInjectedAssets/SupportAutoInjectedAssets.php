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
        if (config('livewire.inject_assets', true) === false) return;

        app('events')->listen(RequestHandled::class, function ($handled) {
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) return;
            if (! method_exists($handled->response, 'status') || $handled->response->status() !== 200) return;
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
        if (str($html)->isMatch(['/<\s*head[^>]*>/', '/<\s*body[^>]*>/'])) {
            return str($html)
                ->replaceMatches('/(<\s*head[^>]*>)/', '$1'.Blade::render('@livewireStyles'))
                ->replaceMatches('/(<\s*\/\s*body\s*>)/', Blade::render('@livewireScripts').'$1')
                ->toString();
        }

        return str($html)
            ->replaceMatches('/(<\s*html[^>]*>)/', '$1'.Blade::render('@livewireStyles'))
            ->replaceMatches('/(<\s*\/\s*html\s*>)/', Blade::render('@livewireScripts').'$1')
            ->toString();
    }
}
