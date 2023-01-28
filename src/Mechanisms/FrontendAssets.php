<?php

namespace Livewire\Mechanisms;

use Livewire\Drawer\Utils;
use Illuminate\Support\Js;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

class FrontendAssets
{
    public $hasRenderedScripts = false;
    public $hasRenderedStyles = false;

    public function boot()
    {
        app()->singleton($this::class);

        Route::get('/livewire/livewire.js', [static::class, 'source']);

        Blade::directive('livewireScripts', [static::class, 'livewireScripts']);
        Blade::directive('livewireStyles', [static::class, 'livewireStyles']);
    }

    public static function livewireScripts($expression)
    {
        return '{!! \Livewire\Mechanisms\FrontendAssets::scripts('.$expression.') !!}';
    }

    public static function livewireStyles($expression)
    {
        return '{!! \Livewire\Mechanisms\FrontendAssets::styles('.$expression.') !!}';
    }

    public function source()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../../dist/livewire.js');
    }

    public function maps()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../../dist/livewire.js.map');
    }

    public static function styles($options = [])
    {
        app(static::class)->hasRenderedStyles = true;

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

        $html = <<<HTML
        <!-- Livewire Styles -->
        <style {$nonce}>
            [wire\:loading], [wire\:loading\.delay], [wire\:loading\.inline-block], [wire\:loading\.inline], [wire\:loading\.block], [wire\:loading\.flex], [wire\:loading\.table], [wire\:loading\.grid], [wire\:loading\.inline-flex] {
                display: none;
            }

            [wire\:loading\.delay\.shortest], [wire\:loading\.delay\.shorter], [wire\:loading\.delay\.short], [wire\:loading\.delay\.long], [wire\:loading\.delay\.longer], [wire\:loading\.delay\.longest] {
                display:none;
            }

            [wire\:offline] {
                display: none;
            }

            [wire\:dirty]:not(textarea):not(input):not(select) {
                display: none;
            }
        </style>
        HTML;

        return static::minify($html);
    }

    public static function scripts($options = [])
    {
        app(static::class)->hasRenderedScripts = true;

        $debug = config('app.debug');

        $scripts = static::js($options);

        // HTML Label.
        $html = $debug ? ['<!-- Livewire Scripts -->'] : [];

        // JavaScript assets.
        $html[] = $debug ? $scripts : static::minify($scripts);

        return implode("\n", $html);
    }

    public static function js($options)
    {
        $assetsUrl = config('livewire.asset_url') ?: rtrim($options['asset_url'] ?? '', '/');

        $appUrl = config('livewire.app_url')
            ?: rtrim($options['app_url'] ?? '', '/')
            ?: $assetsUrl;

        $jsLivewireToken = app()->has('session.store') ? csrf_token() : '';

        $manifest = json_decode(file_get_contents(__DIR__.'/../../dist/manifest.json'), true);
        $versionedFileName = $manifest['/livewire.js'];

        // Default to dynamic `livewire.js` (served by a Laravel route).
        $fullAssetPath = "{$assetsUrl}/livewire{$versionedFileName}";

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

        return <<<HTML
        <script src="{$fullAssetPath}" {$nonce} data-livewire-scripts data-csrf="{$jsLivewireToken}"></script>
        HTML;
    }

    protected static function minify($subject)
    {
        return preg_replace('~(\v|\t|\s{2,})~m', '', $subject);
    }
}
