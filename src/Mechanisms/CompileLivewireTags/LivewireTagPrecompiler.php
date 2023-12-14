<?php

namespace Livewire\Mechanisms\CompileLivewireTags;

use Illuminate\View\Compilers\ComponentTagCompiler;
use Livewire\Drawer\Regexes;
use Livewire\Exceptions\ComponentAttributeMissingOnDynamicComponentException;

class LivewireTagPrecompiler extends ComponentTagCompiler
{
    public function __invoke($value)
    {
        $pattern = '/'.Regexes::$livewireOpeningTagOrSelfClosingTag.'/x';

        return preg_replace_callback($pattern, function (array $matches) {
            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            $keys = array_keys($attributes);
            $values = array_values($attributes);
            $attributesCount = count($attributes);

            for ($i=0; $i < $attributesCount; $i++) {
                if ($keys[$i] === ':' && $values[$i] === 'true') {
                    if (isset($values[$i + 1]) && $values[$i + 1] === 'true') {
                        $attributes[$keys[$i + 1]] = '$'.$keys[$i + 1];
                        unset($attributes[':']);
                    }
                }
            }

            // Convert all kebab-cased to camelCase.
            $attributes = collect($attributes)->mapWithKeys(function ($value, $key) {
                // Skip snake_cased attributes.
                if (str($key)->contains('_')) return [$key => $value];

                return [(string) str($key)->camel() => $value];
            })->toArray();

            // Convert all snake_cased attributes to camelCase, and merge with
            // existing attributes so both snake and camel are available.
            $attributes = collect($attributes)->mapWithKeys(function ($value, $key) {
                // Skip snake_cased attributes
                if (! str($key)->contains('_')) return [$key => false];

                return [(string) str($key)->camel() => $value];
            })->filter()->merge($attributes)->toArray();

            $component = $matches[1];

            if ($component === 'styles') return '@livewireStyles';
            if ($component === 'scripts') return '@livewireScripts';
            if ($component === 'dynamic-component' || $component === 'is') {
                if (! isset($attributes['component']) && ! isset($attributes['is'])) {
                    throw new ComponentAttributeMissingOnDynamicComponentException;
                }

                // Does not need quotes as resolved with quotes already.
                $component = $attributes['component'] ?? $attributes['is'];

                unset($attributes['component'], $attributes['is']);
            } else {
                // Add single quotes to the component name to compile it as string in quotes
                $component = "'{$component}'";
            }

            return $this->componentString($component, $attributes);
        }, $value);
    }

    protected function componentString(string $component, array $attributes)
    {
        if (isset($attributes['key']) || isset($attributes['wire:key'])) {
            $key = $attributes['key'] ?? $attributes['wire:key'];
            unset($attributes['key'], $attributes['wire:key']);

            return "@livewire({$component}, [".$this->attributesToString($attributes, escapeBound: false)."], key({$key}))";
        }

        return "@livewire({$component}, [".$this->attributesToString($attributes, escapeBound: false).'])';
    }

    protected function attributesToString(array $attributes, $escapeBound = true)
    {
        return collect($attributes)
                ->map(function (string $value, string $attribute) use ($escapeBound) {
                    return $escapeBound && isset($this->boundAttributes[$attribute]) && $value !== 'true' && ! is_numeric($value)
                                ? "'{$attribute}' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute({$value})"
                                : "'{$attribute}' => {$value}";
                })
                ->implode(',');
    }
}
