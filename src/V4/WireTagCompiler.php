<?php

namespace Livewire\V4;

use Illuminate\View\Compilers\ComponentTagCompiler;
use Livewire\Exceptions\ComponentAttributeMissingOnDynamicComponentException;

class WireTagCompiler extends ComponentTagCompiler
{
    /**
     * Regex for matching self-closing wire component tags
     */
    protected $wireSelfClosingPattern = '/<\s*livewire:([\w\-\:\.]*)([^>]*)\s*\/\s*>/';

    /**
     * Regex for matching wire component tags with content
     */
    protected $wireOpenClosePattern = '/<\s*livewire:([\w\-\:\.]*)([^>]*)\s*>(.*?)<\/\s*livewire:\1\s*>/s';

    /**
     * Regex for matching livewire:slot tags
     */
    protected $wireSlotPattern = '/
        <\s*livewire:slot
        (?:\s+name=["\']([^"\']+)["\']|\s+name=([^\s>]+))?
        ([^>]*)>
        (.*?)
        <\/\s*livewire:slot\s*>
    /xs';

    public function __invoke($value)
    {
        return $this->compileWireComponents($value);
    }

    /**
     * Compile wire component tags
     */
    protected function compileWireComponents($value)
    {
        // First, handle components with content
        $value = preg_replace_callback($this->wireOpenClosePattern, function (array $matches) {
            $componentName = $matches[1];
            $attributesString = $matches[2] ?? '';
            $content = $matches[3] ?? '';

            $attributes = $this->parseAttributes($attributesString);

            // Handle special components
            if ($componentName === 'styles') return '@livewireStyles';
            if ($componentName === 'scripts') return '@livewireScripts';

            // Handle dynamic components
            if ($componentName === 'dynamic-component' || $componentName === 'is') {
                if (! isset($attributes['component']) && ! isset($attributes['is'])) {
                    throw new ComponentAttributeMissingOnDynamicComponentException;
                }

                $componentName = $attributes['component'] ?? $attributes['is'];
                unset($attributes['component'], $attributes['is']);
            } else {
                // Quote static component names
                $componentName = "'{$componentName}'";
            }

            // Check if content is essentially empty (only whitespace)
            if (trim($content) === '') {
                return $this->compileSimpleComponent($componentName, $attributes);
            }

            // This has content, so we need to handle slots
            return $this->compileComponentWithSlots($componentName, $attributes, $content);
        }, $value);

        // Then, handle self-closing components
        $value = preg_replace_callback($this->wireSelfClosingPattern, function (array $matches) {
            $componentName = $matches[1];
            $attributesString = $matches[2] ?? '';

            $attributes = $this->parseAttributes($attributesString);

            // Handle special components
            if ($componentName === 'styles') return '@livewireStyles';
            if ($componentName === 'scripts') return '@livewireScripts';

            // Handle dynamic components
            if ($componentName === 'dynamic-component' || $componentName === 'is') {
                if (! isset($attributes['component']) && ! isset($attributes['is'])) {
                    throw new ComponentAttributeMissingOnDynamicComponentException;
                }

                $componentName = $attributes['component'] ?? $attributes['is'];
                unset($attributes['component'], $attributes['is']);
            } else {
                // Quote static component names
                $componentName = "'{$componentName}'";
            }

            return $this->compileSimpleComponent($componentName, $attributes);
        }, $value);

        return $value;
    }

    /**
     * Parse attributes from attribute string
     */
    protected function parseAttributes(string $attributeString): array
    {
        $attributes = [];

        if (trim($attributeString) === '') {
            return $attributes;
        }

        // Parse attributes using the parent method
        $parsed = $this->getAttributesFromAttributeString($attributeString);

        return $parsed;
    }

    /**
     * Compile a simple component without slots
     */
    protected function compileSimpleComponent(string $componentName, array $attributes): string
    {
        // Process attributes like the original implementation
        // $attributes = $this->processAttributes($attributes);

        if (isset($attributes['key']) || isset($attributes['wire:key'])) {
            $key = $attributes['key'] ?? $attributes['wire:key'];
            unset($attributes['key'], $attributes['wire:key']);

            return "@livewire({$componentName}, [".$this->attributesToString($attributes, escapeBound: false)."], key({$key}))";
        }

        return "@livewire({$componentName}, [".$this->attributesToString($attributes, escapeBound: false).'])';
    }

    /**
     * Compile a component with slots
     */
    protected function compileComponentWithSlots(string $componentName, array $attributes, string $content): string
    {
        // Extract slots from content
        $slots = $this->extractSlots($content);

        // Process attributes
        // $attributes = $this->processAttributes($attributes);

        // Build the compiled output
        $compiled = '';

        // Initialize slots variable
        $compiled .= "<?php \$__slots = []; ?>\n";

        // Compile each slot with @wireSlot directives
        foreach ($slots as $slotName => $slotData) {
            $compiled .= $this->compileSlot($slotName, $slotData);
        }

        // Add the component call with slots
        $compiled .= $this->compileComponentCall($componentName, $attributes);

        return $compiled;
    }

    /**
     * Extract slots from component content
     */
    protected function extractSlots(string $content): array
    {
        $slots = [];

        // Extract named slots first
        $content = preg_replace_callback($this->wireSlotPattern, function ($matches) use (&$slots) {
            $slotName = $matches[1] ?: $matches[2] ?: 'default';
            $slotAttributes = $matches[3] ?? '';
            $slotContent = $matches[4];

            $slots[$slotName] = [
                'content' => $slotContent,
                'attributes' => $this->parseSlotAttributes($slotAttributes)
            ];

            return ''; // Remove from content
        }, $content);

        // What's left becomes the default slot if not empty
        $defaultContent = trim($content);
        if (!empty($defaultContent) && !isset($slots['default'])) {
            $slots['default'] = [
                'content' => $defaultContent,
                'attributes' => []
            ];
        }

        return $slots;
    }

    /**
     * Compile a slot using @wireSlot directive
     */
    protected function compileSlot(string $slotName, array $slotData): string
    {
        $content = $slotData['content'];
        $attributes = $slotData['attributes'];

        // Recursively compile any wire components within the slot content
        $content = $this->compileWireComponents($content);

        // Build the @wireSlot directive arguments
        $slotArguments = "'{$slotName}'";

        // Add attributes as second argument if any exist
        if (!empty($attributes)) {
            $attributesParts = [];
            foreach ($attributes as $key => $value) {
                if ($value === 'true' || $value === true) {
                    $attributesParts[] = "'{$key}' => true";
                } elseif (is_numeric($value)) {
                    $attributesParts[] = "'{$key}' => {$value}";
                } else {
                    $attributesParts[] = "'{$key}' => '{$value}'";
                }
            }
            $slotArguments .= ', [' . implode(', ', $attributesParts) . ']';
        }

        return "@wireSlot({$slotArguments})\n{$content}\n@endWireSlot\n";
    }

    /**
     * Compile the component call with slots
     */
    protected function compileComponentCall(string $componentName, array $attributes): string
    {
        // Handle livewire:key specially
        $key = null;
        if (isset($attributes['key']) || isset($attributes['livewire:key'])) {
            $key = $attributes['key'] ?? $attributes['livewire:key'];
            unset($attributes['key'], $attributes['livewire:key']);
        }

        // Convert attributes to array format
        $attributesString = $this->attributesToString($attributes, escapeBound: false);

        // Generate the @livewire call with slots
        if ($key) {
            return "@livewire({$componentName}, [{$attributesString}], key({$key}), \$__slots ?? [])";
        }

        return "@livewire({$componentName}, [{$attributesString}], null, \$__slots ?? [])";
    }

    /**
     * Process attributes (convert kebab-case to camelCase, etc.)
     */
    protected function processAttributes(array $attributes): array
    {
        $keys = array_keys($attributes);
        $values = array_values($attributes);
        $attributesCount = count($attributes);

        // Handle special ":" syntax
        for ($i = 0; $i < $attributesCount; $i++) {
            if ($keys[$i] === ':' && $values[$i] === 'true') {
                if (isset($values[$i + 1]) && $values[$i + 1] === 'true') {
                    $attributes[$keys[$i + 1]] = '$' . $keys[$i + 1];
                    unset($attributes[':']);
                }
            }
        }

        // Convert all kebab-cased to camelCase
        $attributes = collect($attributes)->mapWithKeys(function ($value, $key) {
            // Skip snake_cased attributes
            if (str($key)->contains('_')) return [$key => $value];

            return [(string) str($key)->camel() => $value];
        })->toArray();

        // Convert all snake_cased attributes to camelCase, and merge with
        // existing attributes so both snake and camel are available
        $attributes = collect($attributes)->mapWithKeys(function ($value, $key) {
            // Skip snake_cased attributes
            if (! str($key)->contains('_')) return [$key => false];

            return [(string) str($key)->camel() => $value];
        })->filter()->merge($attributes)->toArray();

        return $attributes;
    }

    /**
     * Parse slot attributes
     */
    protected function parseSlotAttributes(string $attributeString): array
    {
        $attributes = [];

        if (trim($attributeString) === '') {
            return $attributes;
        }

        // Parse attributes
        preg_match_all('/
            ([\w\-:]+)                      # Attribute name
            (?:
                \s*=\s*                     # Assignment operator
                (?:
                    "([^"]*)"               # Double quoted value
                    |
                    \'([^\']*)\'            # Single quoted value
                    |
                    ([^\s>]+)               # Unquoted value
                )
            )?
        /x', trim($attributeString), $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match[1];
            $value = $match[2] ?? $match[3] ?? $match[4] ?? 'true';
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    /**
     * Convert attributes array to string
     */
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