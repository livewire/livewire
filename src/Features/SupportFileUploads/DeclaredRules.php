<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Features\SupportFormObjects\Form;

/**
 * Extracts the constraints a property's validation rules declare that can be
 * checked against the metadata the browser sends (filename, declared size and
 * MIME type), so an upload can be rejected before any bytes move.
 *
 * Size constraints (max, min, size, between — all in kilobytes) are verifiable
 * from the declared byte count and mirror Laravel's size validation exactly.
 * Type constraints (image, mimes, mimetypes, extensions) are checked leniently
 * by the planner — Laravel's authoritative check sniffs the real file's
 * contents after upload, so the preflight only rejects files whose declared
 * metadata provably violates the rule. Either way this is a fast-fail
 * courtesy: the declared metadata is client-supplied and the authoritative
 * check happens server-side against the real file.
 */
class DeclaredRules
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

        $size = [];
        $types = [];

        foreach ((array) $rulesForProperty as $rule) {
            if (! is_string($rule)) continue;

            if (preg_match('/^(max|min|size):(\d+)$/', $rule, $matches)) {
                $size[$matches[1]] = (int) $matches[2];
            } elseif (preg_match('/^between:(\d+),(\d+)$/', $rule, $matches)) {
                $size['between'] = [(int) $matches[1], (int) $matches[2]];
            } elseif (preg_match('/^image(?::.+)?$/', $rule)) {
                $types[] = ['rule' => 'image', 'parameters' => []];
            } elseif (preg_match('/^(mimes|mimetypes|extensions):(.+)$/', $rule, $matches)) {
                $types[] = [
                    'rule' => $matches[1],
                    'parameters' => array_map('strtolower', array_map('trim', explode(',', $matches[2]))),
                ];
            }
        }

        return ['size' => $size, 'types' => $types];
    }
}
