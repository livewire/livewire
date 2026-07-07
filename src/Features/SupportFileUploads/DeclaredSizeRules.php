<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Features\SupportFormObjects\Form;

/**
 * Extracts the size-based constraints (max, min, size, between — all in
 * kilobytes) that a property's validation rules declare, so an upload can be
 * rejected from the metadata the browser sends before any bytes move.
 *
 * Only constraints verifiable from a declared byte count are extracted —
 * content rules (image, mimes, dimensions) still run against the real file
 * after it's uploaded. The declared size is client-supplied, so this is a
 * fast-fail courtesy: the authoritative check happens server-side either way.
 */
class DeclaredSizeRules
{
    public static function for($component, $name, $isMultiple)
    {
        // Rules from #[Validate] on form object properties are registered on
        // the form object itself, not the root component...
        $root = (string) str($name)->before('.');

        if (($component->all()[$root] ?? null) instanceof Form) {
            $component = $component->all()[$root];
            $name = (string) str($name)->after('.');
        }

        $rules = $component->getRules();

        // Match nested paths (photos.2) against their wildcard rules (photos.*)...
        $key = $component->ruleWithNumbersReplacedByStars($name);

        $rulesForProperty = $isMultiple
            ? ($rules["{$key}.*"] ?? $rules[$key] ?? [])
            : ($rules[$key] ?? $rules["{$key}.*"] ?? []);

        if (is_string($rulesForProperty)) $rulesForProperty = explode('|', $rulesForProperty);

        $constraints = [];

        foreach ((array) $rulesForProperty as $rule) {
            if (! is_string($rule)) continue;

            if (preg_match('/^(max|min|size):(\d+)$/', $rule, $matches)) {
                $constraints[$matches[1]] = (int) $matches[2];
            } elseif (preg_match('/^between:(\d+),(\d+)$/', $rule, $matches)) {
                $constraints['between'] = [(int) $matches[1], (int) $matches[2]];
            }
        }

        return $constraints;
    }
}
