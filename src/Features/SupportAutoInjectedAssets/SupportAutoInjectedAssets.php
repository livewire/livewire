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
    static $additionalHeadAssets = [];
    static $additionalBodyAssets = [];

    static function injectAdditionalHeadAssets($assetString)
    {
        static::$additionalHeadAssets[] = $assetString;
    }

    static function injectAdditionalBodyAssets($assetString)
    {
        static::$additionalBodyAssets[] = $assetString;
    }

    static function provide()
    {
        on('flush-state', function () {
            static::$hasRenderedAComponentThisRequest = false;
            static::$forceAssetInjection = false;
        });

        app('events')->listen(RequestHandled::class, function ($handled) {
            // If this is a successful HTML response...
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) return;
            if (! method_exists($handled->response, 'status') || $handled->response->status() !== 200) return;

            $assetsHead = '';
            $assetsBody = '';

            // If there are additional head assets, inject those...
            if (count(static::$additionalHeadAssets) > 0) {
                foreach (static::$additionalHeadAssets as $additional) {
                    $assetsHead .= $additional."\n";
                }
            }

            // If there are additional body assets, inject those...
            if (count(static::$additionalBodyAssets) > 0) {
                foreach (static::$additionalBodyAssets as $additional) {
                    $assetsBody .= $additional."\n";
                }
            }

            // If we're injecting Livewire assets...
            if (static::shouldInjectLivewireAssets()) {
                $assetsHead .= FrontendAssets::styles()."\n";
                $assetsBody .= FrontendAssets::scripts()."\n";
            }

            $html = $handled->response->getContent();

            if (str($html)->contains('</html>')) {
                $handled->response->setContent(static::injectAssets($html, $assetsHead, $assetsBody));
            }
        });
    }

    protected static function shouldInjectLivewireAssets()
    {
        if (! static::$forceAssetInjection && config('livewire.inject_assets', true) === false) return false;
        if ((! static::$hasRenderedAComponentThisRequest) && (! static::$forceAssetInjection)) return false;
        if (app(FrontendAssets::class)->hasRenderedScripts) return false;

        return true;
    }

    protected static function getLivewireAssets()
    {
        $livewireStyles = FrontendAssets::styles();
        $livewireScripts = FrontendAssets::scripts();
    }

    public function dehydrate()
    {
        static::$hasRenderedAComponentThisRequest = true;
    }

    static function injectAssets($html, $assetsHead, $assetsBody)
    {
        $html = str($html);

        if ($html->test('/<\s*\/\s*head\s*>/i') && $html->test('/<\s*\/\s*body\s*>/i')) {
            return $html
                ->replaceMatches('/(<\s*\/\s*head\s*>)/i', $assetsHead.'$1')
                ->replaceMatches('/(<\s*\/\s*body\s*>)/i', $assetsBody.'$1')
                ->toString();
        }

        return $html
            ->replaceMatches('/(<\s*html(?:\s[^>])*>)/i', '$1'.$assetsHead)
            ->replaceMatches('/(<\s*\/\s*html\s*>)/i', $assetsBody.'$1')
            ->toString();
    }
}
