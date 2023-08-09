<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Livewire\ComponentHook;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;

use function Livewire\on;

class SupportAutoInjectedAssets extends ComponentHook
{
    static $hasRenderedAComponentThisRequest = false;
    static $forceAssetInjection = false;

    static function provide()
    {
        on('flush-state', function () {
            static::$hasRenderedAComponentThisRequest = false;
            static::$forceAssetInjection = false;
        });

        if (config('livewire.inject_assets', true) === false) return;

        app('events')->listen(RequestHandled::class, function ($handled) {
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) return;
            if (! method_exists($handled->response, 'status') || $handled->response->status() !== 200) return;
            if ((! static::$hasRenderedAComponentThisRequest) && (! static::$forceAssetInjection)) return;
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
        $livewireStyles = FrontendAssets::styles();
        $livewireScripts = FrontendAssets::scripts();

        $html = str($html);

        if ($html->test('/<\s*\/\s*head\s*>/i') && $html->test('/<\s*\/\s*body\s*>/i')) {
            return $html
                ->replaceMatches('/(<\s*\/\s*head\s*>)/i', $livewireStyles.'$1')
                ->replaceMatches('/(<\s*\/\s*body\s*>)/i', $livewireScripts.'$1')
                ->toString();
        }

        return $html
            ->replaceMatches('/(<\s*html(?:\s[^>])*>)/i', '$1'.$livewireStyles)
            ->replaceMatches('/(<\s*\/\s*html\s*>)/i', $livewireScripts.'$1')
            ->toString();
    }
}
