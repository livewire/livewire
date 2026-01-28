<?php

namespace Livewire\Mechanisms\FrontendAssets;

use Illuminate\Support\Facades\Vite;
use Livewire\Drawer\Utils;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Livewire\Mechanisms\Mechanism;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
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
            return config('app.debug')
                ? Route::get(EndpointResolver::scriptPath(minified: false), $handle)
                : Route::get(EndpointResolver::scriptPath(minified: true), $handle);
        });

        Route::get(EndpointResolver::mapPath(csp: false), [static::class, 'maps']);
        Route::get(EndpointResolver::mapPath(csp: true), [static::class, 'cspMaps']);

        Blade::directive('livewireScripts', [static::class, 'livewireScripts']);
        Blade::directive('livewireScriptConfig', [static::class, 'livewireScriptConfig']);
        Blade::directive('livewireStyles', [static::class, 'livewireStyles']);

        app('livewire')->provide(function() {
            $this->publishes(
                [
                    __DIR__.'/../../../dist' => public_path('vendor/livewire'),
                ],
                'livewire:assets',
            );
        });

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
        $isCsp = app('livewire')->isCspSafe();

        if (config('app.debug')) {
            $file = $isCsp ? 'livewire.csp.js' : 'livewire.js';
        } else {
            $file = $isCsp ? 'livewire.csp.min.js' : 'livewire.min.js';
        }

        return Utils::pretendResponseIsFile(__DIR__.'/../../../dist/'.$file);
    }

    public function maps()
    {
        $file = app('livewire')->isCspSafe()
            ? 'livewire.csp.min.js.map'
            : 'livewire.min.js.map';

        return Utils::pretendResponseIsFile(__DIR__.'/../../../dist/'.$file);
    }

    public function cspMaps()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../../../dist/livewire.csp.min.js.map');
    }

    /**
     * @return string
     */
    public static function styles($options = [])
    {
        if (app(static::class)->hasRenderedStyles) return '';

        app(static::class)->hasRenderedStyles = true;

        $nonce = static::nonce($options);
        $nonce = $nonce ? "{$nonce} data-livewire-style" : '';

        $progressBarColor = config('livewire.navigate.progress_bar_color', '#2299dd');

        // Note: the attribute selectors are "doubled" so that they don't get overriden when Tailwind's CDN loads a script tag
        // BELOW the one Livewire injects...
        $html = <<<HTML
        <!-- Livewire Styles -->
        <style {$nonce}>
            [wire\:loading][wire\:loading], [wire\:loading\.delay][wire\:loading\.delay], [wire\:loading\.list-item][wire\:loading\.list-item], [wire\:loading\.inline-block][wire\:loading\.inline-block], [wire\:loading\.inline][wire\:loading\.inline], [wire\:loading\.block][wire\:loading\.block], [wire\:loading\.flex][wire\:loading\.flex], [wire\:loading\.table][wire\:loading\.table], [wire\:loading\.grid][wire\:loading\.grid], [wire\:loading\.inline-flex][wire\:loading\.inline-flex] {
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

            [wire\:cloak] {
                display: none !important;
            }

            dialog#livewire-error::backdrop {
                background-color: rgba(0, 0, 0, .6);
            }
        </style>
        HTML;

        return static::minify($html);
    }

    /**
     * @return string
     */
    public static function scripts($options = [])
    {
        if (app(static::class)->hasRenderedScripts) return '';

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
        $url = url(app(static::class)->javaScriptRoute->uri);

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
        $url = $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . "id={$versionHash}";

        $token = app()->has('session.store') ? csrf_token() : '';

        $assetWarning = null;

        $nonce = static::nonce($options);

        [$url, $assetWarning] = static::usePublishedAssetsIfAvailable($url, $manifest, $nonce);

        $progressBar = config('livewire.navigate.show_progress_bar', true) ? '' : 'data-no-progress-bar';

        $moduleUrl = url(app('livewire')->getUriPrefix());

        $updateUri = url(app('livewire')->getUpdateUri());

        $extraAttributes = Utils::stringifyHtmlAttributes(
            app(static::class)->scriptTagAttributes,
        );

        return <<<HTML
        {$assetWarning}<script src="{$url}" {$nonce} {$progressBar} data-csrf="{$token}" data-module-url="{$moduleUrl}" data-update-uri="{$updateUri}" {$extraAttributes}></script>
        HTML;
    }

    public static function scriptConfig($options = [])
    {
        app(static::class)->hasRenderedScripts = true;

        $nonce = static::nonce($options);

        $progressBar = config('livewire.navigate.show_progress_bar', true) ? '' : 'data-no-progress-bar';

        $attributes = json_encode([
            'csrf' => app()->has('session.store') ? csrf_token() : '',
            'uri' => url(app('livewire')->getUpdateUri()),
            'moduleUrl' => url(app('livewire')->getUriPrefix()),
            'progressBar' => $progressBar,
            'nonce' => isset($options['nonce']) ? $options['nonce'] : '',
        ]);

        return <<<HTML
        <script {$nonce} data-navigate-once="true">window.livewireScriptConfig = {$attributes};</script>
        HTML;
    }

    protected static function usePublishedAssetsIfAvailable($url, $manifest, $nonce)
    {
        $assetWarning = null;

        // Check to see if static assets have been published...
        if (! file_exists(public_path('vendor/livewire/manifest.json'))) {
            return [$url, $assetWarning];
        }

        $publishedManifest = json_decode(file_get_contents(public_path('vendor/livewire/manifest.json')), true);
        $version = $publishedManifest['/livewire.js'];

        $isCsp = app('livewire')->isCspSafe();

        if (config('app.debug')) {
            $fileName = $isCsp ? '/livewire.csp.js' : '/livewire.js';
        } else {
            $fileName = $isCsp ? '/livewire.csp.min.js' : '/livewire.min.js';
        }

        $versionedFileName = "{$fileName}?id={$version}";

        $configuredUrl = config('livewire.asset_url');
        $versionedConfiguredUrl = $configuredUrl
            ? $configuredUrl . (parse_url($configuredUrl, PHP_URL_QUERY) ? '&' : '?') . "id={$version}"
            : null;

        $assetUrl = $versionedConfiguredUrl
            ?? (app('livewire')->isRunningServerless()
                ? rtrim(config('app.asset_url'), '/')."/vendor/livewire$versionedFileName"
                : url("vendor/livewire{$versionedFileName}")
            );

        $url = $assetUrl;

        if ($manifest !== $publishedManifest) {
            $assetWarning = <<<HTML
            <script {$nonce}>
                console.warn('Livewire: The published Livewire assets are out of date\\n See: https://livewire.laravel.com/docs/installation#publishing-livewires-frontend-assets')
            </script>\n
            HTML;
        }

        return [$url, $assetWarning];
    }

    protected static function minify($subject)
    {
        return preg_replace('~(\v|\t|\s{2,})~m', '', $subject);
    }

    protected static function nonce($options = [])
    {
        $nonce = $options['nonce'] ?? Vite::cspNonce();

        return $nonce ? "nonce=\"{$nonce}\"" : '';
    }
}
