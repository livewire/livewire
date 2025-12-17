<?php

namespace Livewire\Features\SupportJson;

use function Livewire\on;
use Livewire\ComponentHook;

use Illuminate\Validation\ValidationException;

class SupportJson extends ComponentHook
{
    public static function provide()
    {
        on('call', function ($component, $method, $params, $context, $returnEarly, $metadata, $index) {
            if (! static::isJsonMethod($component, $method)) return;

            $component->skipRender();

            try {
                $result = $component->{$method}(...$params);

                $returnEarly($result);
            } catch (ValidationException $e) {
                // Add validation errors to returnsMeta effect keyed by action index
                $existingMeta = $context->effects['returnsMeta'] ?? [];
                $existingMeta[$index] = ['errors' => $e->errors()];
                $context->addEffect('returnsMeta', $existingMeta);

                // Return null so the returns array stays aligned
                $returnEarly(null);
            }
        });
    }

    protected static function isJsonMethod($component, $method)
    {
        return $component->getAttributes()
            ->filter(fn ($attr) => $attr instanceof BaseJson)
            ->filter(fn ($attr) => $attr->getName() === $method)
            ->isNotEmpty();
    }
}
