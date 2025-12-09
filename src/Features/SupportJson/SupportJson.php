<?php

namespace Livewire\Features\SupportJson;

use Livewire\ComponentHook;
use Illuminate\Validation\ValidationException;

use function Livewire\on;

class SupportJson extends ComponentHook
{
    public static function provide()
    {
        on('call', function ($component, $method, $params, $context, $returnEarly, $metadata) {
            if (! static::isJsonMethod($component, $method)) return;

            $component->skipRender();

            try {
                $result = $component->{$method}(...$params);

                $returnEarly([$result, null]);
            } catch (ValidationException $e) {
                $returnEarly([null, $e->errors()]);
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
