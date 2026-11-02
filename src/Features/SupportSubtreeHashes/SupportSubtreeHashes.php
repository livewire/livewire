<?php

namespace Livewire\Features\SupportSubtreeHashes;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportSubtreeHashes extends ComponentHook
{
    // Hashes of keyed subtrees the browser currently holds, by component id...
    public static $known = [];

    static function provide()
    {
        on('request', function ($payload) {
            static::$known = [];

            if (! static::enabled()) return;

            foreach ($payload as $component) {
                $hashes = SubtreeHasher::parse($component['hashes'] ?? null);

                if (empty($hashes)) continue;

                $id = json_decode($component['snapshot'], associative: true)['memo']['id'] ?? null;

                if (! is_string($id)) continue;

                static::$known[$id] = $hashes;
            }
        });

        // On mount, only annotate: the page's initial HTML has nothing to skip yet. This runs on the
        // finished HTML, since render hooks each get the original and the last replacement would win...
        on('mount', function () {
            if (! static::enabled()) return;

            return fn ($html) => is_string($html) ? (new SubtreeHasher(static::minimumBytes()))->process($html) : $html;
        });

        on('flush-state', fn () => static::$known = []);
    }

    static function enabled()
    {
        return (bool) config('livewire.skip_unchanged.enabled', false);
    }

    // Small elements cost about as much to hash and claim as they'd save...
    static function minimumBytes()
    {
        return (int) config('livewire.skip_unchanged.min_size', 1024);
    }

    protected $updating = false;

    function hydrate()
    {
        $this->updating = true;
    }

    // On update, placeholders only ever go into the component's own HTML effect. Other renders
    // (e.g. partials a package morphs itself) never see one, since nothing there would skip it...
    function dehydrate($context)
    {
        if (! static::enabled() || ! $this->updating) return;

        $known = static::$known[$this->component->getId()] ?? [];

        $html = $context->effects['html'] ?? null;

        if (is_string($html)) $context->addEffect('html', (new SubtreeHasher(static::minimumBytes()))->process($html, $known));

        // Islands morph into place the same way, but appended or prepended ones are inserted as-is...
        $fragments = $context->effects['islandFragments'] ?? null;

        if (! is_array($fragments)) return;

        $context->addEffect('islandFragments', array_map(function ($fragment) use ($known) {
            if (! is_string($fragment) || ! preg_match('/^<!--\[if FRAGMENT:[^\]]*\bmode=morph\b/', $fragment)) return $fragment;

            return (new SubtreeHasher(static::minimumBytes()))->process($fragment, $known, hasRoot: false);
        }, $fragments));
    }
}
