<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Livewire\Drawer\Utils;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Livewire\Mechanisms\Mechanism;
use function Livewire\on;

class FrontendAssets extends Mechanism
{
    public $hasRenderedScripts = false;
    public $hasRenderedStyles = false;

    public $javaScriptRoute;

    public $scriptTagAttributes = [];

    public function boot()
    {
        app($this::class)->setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire.js', $handle);
        });

        Blade::directive('livewireScripts', [static::class, 'livewireScripts']);
        Blade::directive('livewireScriptConfig', [static::class, 'livewireScriptConfig']);
        Blade::directive('livewireStyles', [static::class, 'livewireStyles']);

        on('flush-state', function () {
            $instance = app(static::class);

            $instance->hasRenderedScripts = false;
            $instance->hasRenderedStyles = false;
        });
    }

    function useScriptTagAttributes($attributes)
    {
        $this->scriptTagAttributes = array_merge($this->scriptTagAttributes, $attributes);
    }

    function setScriptRoute($callback)
    {
        $route = $callback([self::class, 'returnJavaScriptAsFile']);

        $this->javaScriptRoute = $route;
    }

    public static function livewireScripts($expression)
    {
        return '{!! \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts('.$expression.') !!}';
    }

    public static function livewireScriptConfig($expression)
    {
        return '{!! \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scriptConfig('.$expression.') !!}';
    }

    public static function livewireStyles($expression)
    {
        return '{!! \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles('.$expression.') !!}';
    }

    public function returnJavaScriptAsFile()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../../../dist/livewire.js');
    }

    public function maps()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../../../dist/livewire.js.map');
    }

    public static function styles($options = [])
    {
        app(static::class)->hasRenderedStyles = true;

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\" data-livewire-style" : '';

        $progressBarColor = config('livewire.navigate.progress_bar_color', '#2299dd');

        // Note: the attribute selectors are "doubled" so that they don't get overriden when Tailwind's CDN loads a script tag
        // BELOW the one Livewire injects...
        $html = <<<HTML
        <!-- Livewire Styles -->
        <style {$nonce}>
            [wire\:loading][wire\:loading], [wire\:loading\.delay][wire\:loading\.delay], [wire\:loading\.inline-block][wire\:loading\.inline-block], [wire\:loading\.inline][wire\:loading\.inline], [wire\:loading\.block][wire\:loading\.block], [wire\:loading\.flex][wire\:loading\.flex], [wire\:loading\.table][wire\:loading\.table], [wire\:loading\.grid][wire\:loading\.grid], [wire\:loading\.inline-flex][wire\:loading\.inline-flex] {
                display: none;
            }

            [wire\:loading\.delay\.none][wire\:loading\.delay\.none], [wire\:loading\.delay\.shortest][wire\:loading\.delay\.shortest], [wire\:loading\.delay\.shorter][wire\:loading\.delay\.shorter], [wire\:loading\.delay\.short][wire\:loading\.delay\.short], [wire\:loading\.delay\.default][wire\:loading\.delay\.default], [wire\:loading\.delay\.long][wire\:loading\.delay\.long], [wire\:loading\.delay\.longer][wire\:loading\.delay\.longer], [wire\:loading\.delay\.longest][wire\:loading\.delay\.longest] {
                display: none;
            }

            [wire\:offline][wire\:offline] {
                display: none;
            }

            [wire\:dirty]:not(textarea):not(input):not(select) {
                display: none;
            }

            :root {
                --livewire-progress-bar-color: {$progressBarColor};
            }

            [x-cloak] {
                display: none !important;
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

        $html[] = $scripts;

        return implode("\n", $html);
    }

    public static function js($options)
    {
        // Use the default endpoint...
        $url = app(static::class)->javaScriptRoute->uri;

        // Use the configured one...
        $url = config('livewire.asset_url') ?: $url;

        // Use the legacy passed in one...
        $url = $options['asset_url'] ?? $url;

        // Use the new passed in one...
        $url = $options['url'] ?? $url;

        $url = rtrim($url, '/');

        $url = (string) str($url)->when(! str($url)->isUrl(), fn($url) => $url->start('/'));

        // Add the build manifest hash to it...
        $manifest = json_decode(file_get_contents(__DIR__.'/../../../dist/manifest.json'), true);
        $versionHash = $manifest['/livewire.js'];
        $url = "{$url}?id={$versionHash}";

        $token = app()->has('session.store') ? csrf_token() : '';

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

        $progressBar = config('livewire.navigate.show_progress_bar', true) ? '' : 'data-no-progress-bar';

        $updateUri = app('livewire')->getUpdateUri();

        $extraAttributes = Utils::stringifyHtmlAttributes(
            app(static::class)->scriptTagAttributes,
        );

        return <<<HTML
        <script src="{$url}" {$nonce} {$progressBar} data-csrf="{$token}" data-update-uri="{$updateUri}" {$extraAttributes}></script>
        HTML;
    }

    public static function scriptConfig($options = [])
    {
        app(static::class)->hasRenderedScripts = true;

        $nonce = isset($options['nonce']) ? " nonce=\"{$options['nonce']}\"" : '';

        $progressBar = config('livewire.navigate.show_progress_bar', true) ? '' : 'data-no-progress-bar';

        $attributes = json_encode([
            'csrf' => app()->has('session.store') ? csrf_token() : '',
            'uri' => app('livewire')->getUpdateUri(),
            'progressBar' => $progressBar,
            'nonce' => isset($options['nonce']) ? $options['nonce'] : '',
        ]);

        return <<<HTML
        <script{$nonce} data-navigate-once="true">window.livewireScriptConfig = {$attributes};</script>
        HTML;
    }

    protected static function minify($subject)
    {
        return preg_replace('~(\v|\t|\s{2,})~m', '', $subject);
    }
}
