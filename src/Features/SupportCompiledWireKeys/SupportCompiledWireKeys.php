<?php

namespace Livewire\Features\SupportCompiledWireKeys;

use Illuminate\Support\Facades\Blade;
use Livewire\ComponentHook;

use function Livewire\on;

class SupportCompiledWireKeys extends ComponentHook
{
    public static $loopStack = [];
    public static $currentLoop = [
        'count' => null,
        'index' => null,
        'key' => null,
    ];

    public static function provide()
    {
        on('flush-state', function () {
            static::$loopStack = [];
            static::$currentLoop = [
                'count' => null,
                'index' => null,
                'key' => null,
            ];
        });

        if (! config('livewire.smart_wire_keys', true)) {
            return;
        }

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
            $prefix = "<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('{$escapedKey}', get_defined_vars()); ?>";
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

    public static function openLoop() {
        if (static::$currentLoop['count'] === null) {
            static::$currentLoop['count'] = 0;
        } else {
            static::$currentLoop['count']++;
        }

        static::$loopStack[] = static::$currentLoop;

        static::$currentLoop = [
            'count' => null,
            'index' => null,
            'key' => null,
        ];
    }

    public static function startLoop($index) {
        static::$currentLoop['index'] = $index;
    }

    public static function endLoop() {
        static::$currentLoop = [
            'count' => null,
            'index' => null,
            'key' => null,
        ];
    }

    public static function closeLoop() {
        static::$currentLoop = array_pop(static::$loopStack);
    }

    public static function processElementKey($keyString, $data)
    {
        // If the key string matches an existing view name, return it as-is.
        // This prevents Blade::render() from incorrectly rendering a view
        // when the user just wants a literal string key like "account".
        if (view()->exists($keyString)) {
            $key = $keyString;
        } else {
            $key = Blade::render($keyString, $data);
        }

        static::$currentLoop['key'] = $key;
    }

    public static function processComponentKey($component)
    {
        if ($component->attributes->has('wire:key')) {
            static::$currentLoop['key'] = $component->attributes->get('wire:key');
        }
    }

    public static function generateKey($deterministicBladeKey, $key = null)
    {
        $finalKey = $deterministicBladeKey;

        $loops = array_merge(static::$loopStack, [static::$currentLoop]);

        foreach ($loops as $loop) {
            if (isset($loop['key']) || isset($loop['index'])) {
                $finalKey .= isset($loop['key'])
                    ? '-' . $loop['key']
                    : '-' . $loop['index'];
            }

            if (isset($loop['count'])) {
                $finalKey .= '-' . $loop['count'];
            }
        }

        if (isset($key) && $key !== '') {
            $finalKey .= '-' . $key;
        }

        return $finalKey;
    }
}
