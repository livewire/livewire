<?php

namespace Livewire;

class Assets
{
    public function source()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../dist/livewire.js');
    }

    public function maps()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../dist/livewire.js.map');
    }

    public static function styles($options = [])
    {
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
        $debug = config('app.debug');

        $scripts = static::javaScriptAssets($options);

        // HTML Label.
        $html = $debug ? ['<!-- Livewire Scripts -->'] : [];

        // JavaScript assets.
        $html[] = $debug ? $scripts : static::minify($scripts);

        return implode("\n", $html);
    }

    public static function javaScriptAssets($options)
    {
        $jsonEncodedOptions = $options ? json_encode($options) : '';

        $assetsUrl = config('livewire.asset_url') ?: rtrim($options['asset_url'] ?? '', '/');

        $appUrl = config('livewire.app_url')
            ?: rtrim($options['app_url'] ?? '', '/')
            ?: $assetsUrl;

        $jsLivewireToken = app()->has('session.store') ? "'" . csrf_token() . "'" : 'null';

        $manifest = json_decode(file_get_contents(__DIR__.'/../dist/manifest.json'), true);
        $versionedFileName = $manifest['/livewire.js'];

        // Default to dynamic `livewire.js` (served by a Laravel route).
        $fullAssetPath = "{$assetsUrl}/livewire{$versionedFileName}";
        $assetWarning = null;

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

	    $devTools = null;
	    $windowLivewireCheck = null;
        if (config('app.debug')) {
	        $devTools = 'window.Livewire.devTools(true);';

	        $windowLivewireCheck = <<<'HTML'
            if (window.Livewire) {
                console.warn('Livewire: It looks like Livewire\'s @livewireScripts JavaScript assets have already been loaded. Make sure you aren\'t loading them twice.')
            }
            HTML;
        }

        // Because it will be minified, using semicolons is important.
        return <<<HTML
        {$assetWarning}
        <script src="{$fullAssetPath}" data-turbo-eval="false" data-turbolinks-eval="false" x-navigate:ignore {$nonce}></script>
        <script data-turbo-eval="false" data-turbolinks-eval="false" x-navigate:ignore {$nonce}>
            {$windowLivewireCheck}

            window.livewire_app_url = '{$appUrl}';
            window.livewire_token = {$jsLivewireToken};

            // {$devTools}

            let started = false;

            window.addEventListener('alpine:init', function () {
                if (! started) {
                    window.Livewire.start();

                    started = true;
                }
            });

            document.addEventListener('alpine:navigated', function () {
                // window.livewire.restart();
            });
        </script>
        HTML;
    }

    protected function minify($subject)
    {
        return preg_replace('~(\v|\t|\s{2,})~m', '', $subject);
    }
}
