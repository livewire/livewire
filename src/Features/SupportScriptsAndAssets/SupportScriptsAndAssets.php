<?php

namespace Livewire\Features\SupportScriptsAndAssets;

use Illuminate\Support\Facades\Blade;
use function Livewire\store;
use Livewire\ComponentHook;

use function Livewire\on;

class SupportScriptsAndAssets extends ComponentHook
{
    public static $alreadyRunAssetKeys = [];

    public static $countersByViewPath = [];

    public static $renderedAssets = [];

    public static $nonLivewireAssets = [];

    public static function getAssets()
    {
        return static::$renderedAssets;
    }

    public static function processNonLivewireAssets()
    {
        // If any assets have been added outside of a Livewire component, then they will not be 
        // processed like the other assets as there is no dehydrate being called. So instead 
        // we process them manually that way they are included with the other assets when
        // they are injected...
        $alreadyRunAssetKeys = [];

        foreach (static::$nonLivewireAssets as $key => $assets) {
             if (! in_array($key, $alreadyRunAssetKeys)) {

                // These will get injected into the HTML if it's an initial page load...
                static::$renderedAssets[$key] = $assets;

                $alreadyRunAssetKeys[] = $key;
            }
        }
    }

    public static function getUniqueBladeCompileTimeKey()
    {
        // Rather than using random strings as compile-time keys for blade directives,
        // we want something more detereminstic to protect against problems that arise
        // from using load-balancers and such.
        // Therefore, we create a key based on the currently compiling view path and
        // number of already compiled directives here...
        $viewPath = crc32(app('blade.compiler')->getPath() ?? '');

        if (! isset(static::$countersByViewPath[$viewPath])) static::$countersByViewPath[$viewPath] = 0;

        $key = $viewPath.'-'.static::$countersByViewPath[$viewPath];

        static::$countersByViewPath[$viewPath]++;

        return $key;
    }

    static function provide()
    {
        on('flush-state', function () {
            static::$alreadyRunAssetKeys = [];
            static::$countersByViewPath = [];
            static::$renderedAssets = [];
            static::$nonLivewireAssets = [];
        });

        Blade::directive('script', function () {
            $key = static::getUniqueBladeCompileTimeKey();

            return <<<PHP
                <?php
                    \$__scriptKey = '$key';
                    ob_start();
                ?>
            PHP;
        });

        Blade::directive('endscript', function () {
            return <<<PHP
                <?php
                    \$__output = ob_get_clean();

                    \Livewire\store(\$this)->push('scripts', \$__output, \$__scriptKey)
                ?>
            PHP;
        });

        Blade::directive('assets', function () {
            $key = static::getUniqueBladeCompileTimeKey();

            return <<<PHP
                <?php
                    \$__assetKey = '$key';

                    ob_start();
                ?>
            PHP;
        });

        Blade::directive('endassets', function () {
            return <<<PHP
                <?php
                    \$__output = ob_get_clean();

                    // If the asset has already been loaded anywhere during this request, skip it...
                    if (in_array(\$__assetKey, \Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets::\$alreadyRunAssetKeys)) {
                        // Skip it...
                    } else {
                        \Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets::\$alreadyRunAssetKeys[] = \$__assetKey;

                        // Check if we're in a Livewire component or not and store the asset accordingly...
                        if (isset(\$this)) {
                            \Livewire\store(\$this)->push('assets', \$__output, \$__assetKey);
                        } else {
                            \Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets::\$nonLivewireAssets[\$__assetKey] = \$__output;
                        }
                    }
                ?>
            PHP;
        });
    }

    function hydrate($memo) {
        // Store the "scripts" and "assets" memos so they can be re-added later (persisted between requests)...
        if (isset($memo['scripts'])) {
            store($this->component)->set('forwardScriptsToDehydrateMemo', $memo['scripts']);
        }

        if (isset($memo['assets'])) {
            store($this->component)->set('forwardAssetsToDehydrateMemo', $memo['assets']);
        }
    }

    function dehydrate($context)
    {
        $alreadyRunScriptKeys = store($this->component)->get('forwardScriptsToDehydrateMemo', []);

        // Add any scripts to the payload that haven't been run yet for this component....
        foreach (store($this->component)->get('scripts', []) as $key => $script) {
            if (! in_array($key, $alreadyRunScriptKeys)) {
                $context->pushEffect('scripts', $script, $key);
                $alreadyRunScriptKeys[] = $key;
            }
        }

        $context->addMemo('scripts', $alreadyRunScriptKeys);

        // Add any assets to the payload that haven't been run yet for the entire page...

        $alreadyRunAssetKeys = store($this->component)->get('forwardAssetsToDehydrateMemo', []);

        foreach (store($this->component)->get('assets', []) as $key => $assets) {
            if (! in_array($key, $alreadyRunAssetKeys)) {

                // These will either get injected into the HTML if it's an initial page load
                // or they will be added to the "assets" key in an ajax payload...
                static::$renderedAssets[$key] = $assets;

                $alreadyRunAssetKeys[] = $key;
            }
        }

        $context->addMemo('assets', $alreadyRunAssetKeys);
    }
}
