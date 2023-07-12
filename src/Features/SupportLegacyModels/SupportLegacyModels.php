<?php

namespace Livewire\Features\SupportLegacyModels;

use Livewire\ComponentHook;

use function Livewire\on;

/**
 * Depends on: SupportValidation for ->missingRuleFor() method on component. (inside ModelSynth)
 */
class SupportLegacyModels extends ComponentHook
{
    protected static $rules;

    static function provide()
    {
        // Only enable this feature if config option is set to `true`.
        if (config('livewire.legacy_model_binding', false) !== true) return;

        app('livewire')->propertySynthesizer([
            EloquentModelSynth::class,
            EloquentCollectionSynth::class,
        ]);

        on('flush-state', function() {
            static::flushRules();
        });
    }

    static function flushRules()
    {
        static::$rules = null;
    }

    static function hasRuleFor($component, $path) {
        $path = str($path)->explode('.');

        $has = false;
        $end = false;

        $segmentRules = static::getRules($component);

        foreach ($path as $key => $segment) {
            if ($end) {
                throw new \LogicException('Something went wrong');
            }

            if (!is_numeric($segment) && array_key_exists($segment, $segmentRules)) {
                $segmentRules = $segmentRules[$segment];
                $has = true;
                continue;
            }

            if (is_numeric($segment) && array_key_exists('*', $segmentRules)) {
                $segmentRules = $segmentRules['*'];
                $has = true;
                continue;
            }

            if (is_numeric($segment) && in_array('*', $segmentRules, true)) {
                $has = true;
                $end = true;
                continue;
            }

            if (in_array($segment, $segmentRules, true)) {
                $has = true;
                $end = true;
                continue;
            }

            $has = false;
        }

        return $has;
    }

    static function missingRuleFor($component, $path) {
        return ! static::hasRuleFor($component, $path);
    }

    static function getRules($component) {
        return static::$rules[$component->getId()] ??= static::processRules($component);
    }

    static function getRulesFor($component, $key)
    {
        $rules = static::getRules($component);

        $propertyWithStarsInsteadOfNumbers = static::ruleWithNumbersReplacedByStars($key);

        return static::dataGetWithoutWildcardSupport(
            $rules,
            $propertyWithStarsInsteadOfNumbers,
            data_get($rules, $key, []),
        );
    }

    static function dataGetWithoutWildcardSupport($array, $key, $default)
    {
        $segments = explode('.', $key);

        $first = array_shift($segments);

        if (! isset($array[$first])) {
            return value($default);
        }

        $value = $array[$first];

        if (count($segments) > 0) {
            return static::dataGetWithoutWildcardSupport($value, implode('.', $segments), $default);
        }

        return $value;
    }

    static function ruleWithNumbersReplacedByStars($dotNotatedProperty)
    {
        // Convert foo.0.bar.1 -> foo.*.bar.*
        return (string) str($dotNotatedProperty)
            // Replace all numeric indexes with an array wildcard: (.0., .10., .007.) => .*.
            // In order to match overlapping numerical indexes (foo.1.2.3.4.name),
            // We need to use a positive look-behind, that's technically all the magic here.
            // For better understanding, see: https://regexr.com/5d1n3
            ->replaceMatches('/(?<=(\.))\d+\./', '*.')
            // Replace all numeric indexes at the end of the name with an array wildcard
            // (Same as the previous regex, but ran only at the end of the string)
            // For better undestanding, see: https://regexr.com/5d1n6
            ->replaceMatches('/\.\d+$/', '.*');
    }

    protected static function processRules($component)
    {
        $rules = array_keys($component->getRules());

        return static::convertDotNotationToArrayNotation($rules);
    }

    protected static function convertDotNotationToArrayNotation($rules)
    {
        return static::recusivelyProcessDotNotation($rules);
    }

    protected static function recusivelyProcessDotNotation($rules)
    {
        $singleRules = [];
        $groupedRules = [];

        foreach ($rules as $key => $value) {
            $value = str($value);

            if (!$value->contains('.')) {
                $singleRules[] = (string) $value;

                continue;
            }

            $groupedRules[(string) $value->before('.')][] = (string) $value->after('.');
        }

        foreach ($groupedRules as $key => $value) {
            $groupedRules[$key] = static::recusivelyProcessDotNotation($value);
        }

        return $singleRules + $groupedRules;
    }
}
