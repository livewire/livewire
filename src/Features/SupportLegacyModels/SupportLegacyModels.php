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
        if (config('livewire.eloquent_model_binding', false) !== true) return;

        app('livewire')->synth([
            EloquentModelSynth::class,
            EloquentCollectionSynth::class,
        ]);

        on('flush-state', function() {
            static::flushRules();
        });
    }

    public function dehydrate()
    {
        // Flush rules when component is dehydrated to ensure they're not
        // memoized when the next component is loaded
        $this->flushRules();
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
        // ray('getRulesFor', $key);
        // ray('hasRulesFor', static::hasRuleFor($component, $key));
        // ray(static::getRules($component));
        
        $rules = static::getRules($component);

        return $rules[$key] ?? [];
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
