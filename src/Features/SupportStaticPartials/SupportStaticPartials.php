<?php

namespace Livewire\Features\SupportStaticPartials;

use Illuminate\Support\Facades\Blade;
use function Livewire\store;
use Livewire\ComponentHook;
use function Livewire\on;

class SupportStaticPartials extends ComponentHook
{
    public static $alreadyRunAssetKeys = [];

    public static $countersByViewPath = [];

    public static function getUniqueBladeCompileTimeKey()
    {
        // Rather than using random strings as compile-time keys for blade directives,
        // we want something more detereminstic to protect against problems that arise
        // from using load-balancers and such.
        // Therefore, we create a key based on the currently compiling view path and
        // number of already compiled directives here...
        $viewPath = crc32(app('blade.compiler')->getPath());

        if (! isset(static::$countersByViewPath[$viewPath])) static::$countersByViewPath[$viewPath] = 0;

        $key = $viewPath.'-'.static::$countersByViewPath[$viewPath];

        static::$countersByViewPath[$viewPath]++;

        return $key;
    }

    static $isEnabled = true;

    static function enable()
    {
        static::$isEnabled = true;
    }

    static function provide()
    {
        if (! static::$isEnabled) return;

        on('flush-state', function () {
            static::$alreadyRunAssetKeys = [];
            static::$countersByViewPath = [];
            static::$isEnabled = false;
        });

        Blade::directive('static', function () {
            $key = static::getUniqueBladeCompileTimeKey();

            return "<?php \$this->startStatic('$key'); ?>";
        });

        Blade::directive('endstatic', function () {
            return "<?php echo \$this->endStatic(); ?>";
        });

        Blade::directive('dynamic', function () {
            return "<?php \$this->startDynamic(); ?>";
        });

        Blade::directive('enddynamic', function () {
            return "<?php echo \$this->endDynamic(); ?>";
        });
    }

    function hydrate($memo) {
        // Store the "statics" memos so they can be re-added later (persisted between requests)...
        if (isset($memo['statics'])) {
            $this->component->setPreviousStatics($memo['statics']);
        }
    }

    function dehydrate($context)
    {
        $context->addMemo('statics', $this->component->getAllStatics());
        $context->addEffect('newStatics', $this->component->getNewStatics());
        $context->addEffect('renderedStatics', $this->component->getRenderedStatics());
        return;

















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
                $context->pushEffect('assets', $assets, $key);
                $alreadyRunAssetKeys[] = $key;
            }
        }

        $context->addMemo('assets', $alreadyRunAssetKeys);
    }
}
