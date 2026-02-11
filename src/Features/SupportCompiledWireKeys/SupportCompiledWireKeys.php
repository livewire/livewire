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
            $keyExpression = static::compileKeyExpression($keys[1][$index]);
            $prefix = "<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::\$currentLoop['key'] = {$keyExpression}; ?>";
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

    public static function compileKeyExpression($keyString)
    {
        // Compile Blade echo statements into PHP string concatenation
        // (mirrors Laravel's ComponentTagCompiler::compileAttributeEchos approach)...
        $value = Blade::compileEchos($keyString);

        // Escape single quotes only outside of PHP blocks...
        $value = collect(token_get_all('<'.'?php ?'.'>'.$value))
            ->slice(2)
            ->map(function ($token) {
                if (! is_array($token)) {
                    return $token;
                }

                return $token[0] === T_INLINE_HTML
                    ? str_replace("'", "\\'", $token[1])
                    : $token[1];
            })->implode('');

        $value = str_replace('<'.'?php echo ', "'.", $value);
        $value = str_replace('; ?'.'>', ".'", $value);

        return "'".$value."'";
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
