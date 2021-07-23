<?php

namespace Livewire;

use Illuminate\View\Compilers\ComponentTagCompiler;
use Livewire\Exceptions\ComponentAttributeMissingOnDynamicComponentException;

class LivewireTagCompiler extends ComponentTagCompiler
{
    public function compile($value)
    {
        return $this->compileLivewireSelfClosingTags($value);
    }

    protected function compileLivewireSelfClosingTags($value)
    {
        $pattern = "/
            <
                \s*
                livewire\:([\w\-\:\.]*)
                \s*
                (?<attributes>
                    (?:
                        \s+
                        [\w\-:.@]+
                        (
                            =
                            (?:
                                \\\"[^\\\"]*\\\"
                                |
                                \'[^\']*\'
                                |
                                [^\'\\\"=<>]+
                            )
                        )?
                    )*
                    \s*
                )
            \/?>
        /x";

        return preg_replace_callback($pattern, function (array $matches) {
            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            // Convert kebab attributes to camel-case.
            $attributes = collect($attributes)->mapWithKeys(function ($value, $key) {
                return [(string) str($key)->camel() => $value];
            })->toArray();

            $component = $matches[1];

            if ($component === 'styles') return '@livewireStyles';
            if ($component === 'scripts') return '@livewireScripts';
            if ($component === 'dynamic-component' || $component === 'is') {
                if(! isset($attributes['component'])) {
                    $dynamicComponentExists = rescue(function() use ($component, $attributes) {
                        // Need to run this in rescue otherwise running this during a test causes Livewire directory not found exception
                        return $component === 'dynamic-component' && app('livewire')->getClass('dynamic-component');
                    });

                    if($dynamicComponentExists) {
                        return $this->componentString("'{$component}'", $attributes);
                    }

                    throw new ComponentAttributeMissingOnDynamicComponentException;
                }

                // Does not need quotes as resolved with quotes already.
                $component = $attributes['component'];

                unset($attributes['component']);
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
            unset($attributes['key']);
            unset($attributes['wire:key']);

            return "@livewire({$component}, [".$this->attributesToString($attributes, $escapeBound = false)."], key({$key}))";
        }

        return "@livewire({$component}, [".$this->attributesToString($attributes, $escapeBound = false).'])';
    }
}
