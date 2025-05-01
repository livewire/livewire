<?php

namespace Livewire\Features\SupportCompiledWireKeys;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

use function Livewire\on;

class SupportCompiledWireKeys extends ComponentHook
{
    public static $loopStack = [];

    public static function provide()
    {
        on('flush-state', function () {
            static::$loopStack = [];
        });

        static::registerPrecompilers();
    }

    public static function registerPrecompilers()
    {
        Blade::precompiler(function ($contents) {
            $contents = static::compile($contents);

            return $contents;
        });
    }

    public static function compile($contents)
    {
        // Strip out all livewire tag components as we don't want to match any of them...
        $placeholder = '<__livewire-component-placeholder__>';
        $cleanedContents = preg_replace('/<livewire:[^>]+?\/>/is', $placeholder, $contents);

        // Handle `wire:key` attributes on elements...
        preg_match_all('/(?<=\s)wire:key\s*=\s*(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')/', $cleanedContents, $keys);

        foreach ($keys[0] as $index => $key) {
            $escapedKey = str_replace("'", "\'", $keys[1][$index]);
            $prefix = "<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processKey('{$escapedKey}', get_defined_vars()); ?>";
            $contents = str_replace($key, $prefix . $key, $contents);
        }

        // Handle `wire:key` attributes on Blade components...
        $contents = preg_replace(
            '/(<\?php\s+\$component->withAttributes\(\[.*?\]\);\s*\?>)/s',
            "$1\n<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey(\$component); ?>\n",
            $contents
        );

        return $contents;
    }

    public static function addLoop()
    {
        static::$loopStack[] = [
            'count' => 0,
            'index' => null,
            'key' => null,
            'open' => false,
        ];
    }

    public static function removeLoop()
    {
        array_pop(static::$loopStack);
    }

    public static function openLoop()
    {
        if (count(static::$loopStack)) {
            if (static::$loopStack[count(static::$loopStack) - 1]['open'] === true) {
                static::addLoop();
            } else {
                static::incrementLoop();
            }
        }

        if (count(static::$loopStack) === 0) {
            static::addLoop();
        }

        static::$loopStack[count(static::$loopStack) - 1]['open'] = true;
    }

    public static function closeLoop()
    {
        if (count(static::$loopStack) === 0) {
            return;
        }

        if (static::$loopStack[count(static::$loopStack) - 1]['open'] === false) {
            static::removeLoop();
            static::closeLoop();

            return;
        }

        static::$loopStack[count(static::$loopStack) - 1]['open'] = false;
    }

    public static function incrementLoop()
    {
        if (count(static::$loopStack) === 0) {
            return;
        }

        static::$loopStack[count(static::$loopStack) - 1]['count']++;
    }

    public static function processKey($keyString, $data)
    {
        $key = Blade::render($keyString, $data);

        static::setLoopKey($key);
    }

    public static function processComponentKey($component)
    {
        if ($component->attributes->has('wire:key')) {
            static::setLoopKey($component->attributes->get('wire:key'));
        }
    }

    public static function startLoop($index)
    {
        static::setLoopIndex($index);
    }

    public static function setLoopIndex($index)
    {
        if (count(static::$loopStack) === 0) {
            return;
        }

        static::setValueOnOpenLoop('index', $index);
    }

    public static function setLoopKey($key)
    {
        if (count(static::$loopStack) === 0) {
            return;
        }

        if (isset(static::$loopStack[count(static::$loopStack) - 1]['key'])) {
            return;
        }

        static::setValueOnOpenLoop('key', $key);
    }

    public static function endLoop()
    {
        static::removeClosedLoops();
        static::resetLoop();
    }

    public static function resetLoop()
    {
        if (count(static::$loopStack) === 0) {
            return;
        }

        static::setValueOnOpenLoop('key', null);
        static::setValueOnOpenLoop('index', null);
    }

    public static function generateKey($deterministicBladeKey, $key = null)
    {
        $finalKey = $deterministicBladeKey;

        for ($i = 0; $i < count(static::$loopStack); $i++) {
            if (static::$loopStack[$i]['open'] === false) {
                continue;
            }

            $finalKey .= '-' . static::$loopStack[$i]['count'];

            if (isset(static::$loopStack[$i]['key']) || isset(static::$loopStack[$i]['index'])) {
                $finalKey .= isset(static::$loopStack[$i]['key'])
                    ? '-' . static::$loopStack[$i]['key']
                    : '-' . static::$loopStack[$i]['index'];
            }
        }

        if (isset($key) && $key !== '') {
            $finalKey .= '-' . $key;
        }

        return $finalKey;
    }

    protected static function setValueOnOpenLoop($key, $value)
    {
        if (count(static::$loopStack) === 0) {
            return;
        }

        for ($i = count(static::$loopStack) - 1; $i >= 0; $i--) {
            if (static::$loopStack[$i]['open'] === true) {
                static::$loopStack[$i][$key] = $value;
                break;
            }
        }
    }

    protected static function setValueOnClosedLoop($key, $value)
    {
        if (count(static::$loopStack) === 0) {
            return;
        }

        for ($i = count(static::$loopStack) - 1; $i >= 0; $i--) {
            if (static::$loopStack[$i]['open'] === false) {
                static::$loopStack[$i][$key] = $value;
                break;
            }
        }
    }

    protected static function removeClosedLoops()
    {
        for ($i = count(static::$loopStack) - 1; $i >= 0; $i--) {
            if (static::$loopStack[$i]['open'] === false) {
                static::removeLoop();
            } else {
                break;
            }
        }
    }
}
