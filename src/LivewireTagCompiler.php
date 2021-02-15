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
            if ($component === 'dynamic-component') {
                if(! isset($attributes['component'])) {
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
        if (isset($attributes['key'])) {
            $key = $attributes['key'];
            unset($attributes['key']);

            return "@livewire({$component}, [".$this->attributesToString($attributes, $escapeBound = false)."], key({$key}))";
        }

        return "@livewire({$component}, [".$this->attributesToString($attributes, $escapeBound = false).'])';
    }
}
