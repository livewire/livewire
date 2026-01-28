<?php

namespace Livewire\Mechanisms\CompileLivewireTags;

use Illuminate\View\Compilers\ComponentTagCompiler;
use Livewire\Drawer\Regexes;
use Livewire\Exceptions\ComponentAttributeMissingOnDynamicComponentException;

class LivewireTagPrecompiler extends ComponentTagCompiler
{
    public function __invoke($value)
    {
        $value = $this->compileSlotTags($value);
        $value = $this->compileSlotClosingTags($value);
        $value = $this->compileOpeningTags($value);
        $value = $this->compileClosingTags($value);
        $value = $this->compileSelfClosingTags($value);

        return $value;
    }

    public function compileSlotTags($value)
    {
        $pattern = '/'.Regexes::$slotOpeningTag.'/x';

        return preg_replace_callback($pattern, function (array $matches) {
            $name = $matches['name'] ?? 'default';

            $output = '';

            $output .= '<?php if (isset($__slotName)) { $__slotNameOriginal = $__slotName; } ?>' . PHP_EOL;
            $output .= '<?php $__slotName = '.$name.'; ?>' . PHP_EOL;
            $output .= '<?php ob_start(); ?>' . PHP_EOL;

            return $output;
        }, $value);
    }

    public function compileSlotClosingTags($value)
    {
        $pattern = '/'.Regexes::$slotClosingTag.'/x';

        return preg_replace_callback($pattern, function (array $matches) {
            $output = '';

            $output .= '<?php $__slotContent = ob_get_clean(); ?>' . PHP_EOL;
            $output .= '<?php $__slots[$__slotName] = $__slotContent; ?>' . PHP_EOL;
            $output .= '<?php if (isset($__slotNameOriginal)) { $__slotName = $__slotNameOriginal; unset($__slotNameOriginal); } ?>' . PHP_EOL;

            return $output;
        }, $value);
    }

    public function compileOpeningTags($value)
    {
        $pattern = '/'.Regexes::$livewireOpeningTag.'/x';

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

            $keyInnerContent = null;

            if (isset($attributes['key']) || isset($attributes['wire:key'])) {
                $keyInnerContent = $attributes['key'] ?? $attributes['wire:key'];
                unset($attributes['key'], $attributes['wire:key']);
            } else {
                $keyInnerContent = 'null';
            }

            $attributeInnerContent = '[' . $this->attributesToString($attributes, escapeBound: false) . ']';

            $output = '';

            $output .= '<?php if (isset($__component)) { $__componentOriginal = $__component; } ?>' . PHP_EOL;
            $output .= '<?php if (isset($__key)) { $__keyOriginal = $__key; } ?>' . PHP_EOL;
            $output .= '<?php if (isset($__attributes)) { $__attributesOriginal = $__attributes; } ?>' . PHP_EOL;
            $output .= '<?php if (isset($__slots)) { $__slotsOriginal = $__slots; } ?>' . PHP_EOL;

            $output .= '<?php $__component = '.$component.'; ?>' . PHP_EOL;
            $output .= '<?php $__key = '.$keyInnerContent.'; ?>' . PHP_EOL;
            $output .= '<?php $__attributes = '.$attributeInnerContent.'; ?>' . PHP_EOL;
            $output .= '<?php $__slots = []; ?>' . PHP_EOL;

            $output .= '<?php ob_start(); ?>' . PHP_EOL;

            return $output;
        }, $value);
    }

    public function compileClosingTags($value)
    {
        $pattern = '/'.Regexes::$livewireClosingTag.'/x';

        return preg_replace_callback($pattern, function (array $matches) {
            $output = '';

            $output .= "<?php \$__slots['default'] = ob_get_clean(); ?>" . PHP_EOL;

            $output .= '@livewire($__component, $__attributes, key($__key), $__slots ?? [])' . PHP_EOL;

            $output .= '<?php if (isset($__componentOriginal)) { $__component = $__componentOriginal; unset($__componentOriginal); } ?>' . PHP_EOL;
            $output .= '<?php if (isset($__keyOriginal)) { $__key = $__keyOriginal; unset($__keyOriginal); } ?>' . PHP_EOL;
            $output .= '<?php if (isset($__attributesOriginal)) { $__attributes = $__attributesOriginal; unset($__attributesOriginal); } ?>' . PHP_EOL;
            $output .= '<?php if (isset($__slotsOriginal)) { $__slots = $__slotsOriginal; unset($__slotsOriginal); } ?>' . PHP_EOL;

            return $output;
        }, $value);
    }

    public function compileSelfClosingTags($value)
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

            if (isset($attributes['key']) || isset($attributes['wire:key'])) {
                $key = $attributes['key'] ?? $attributes['wire:key'];
                unset($attributes['key'], $attributes['wire:key']);

                return "@livewire({$component}, [".$this->attributesToString($attributes, escapeBound: false)."], key({$key}))";
            }

            return "@livewire({$component}, [".$this->attributesToString($attributes, escapeBound: false).'])';
        }, $value);
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