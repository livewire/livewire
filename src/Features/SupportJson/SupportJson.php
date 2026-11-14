<?php

namespace Livewire\Features\SupportJson;

use function Livewire\on;
use Livewire\ComponentHook;
use Livewire\Features\SupportAttributes\AttributeLevel;
use Livewire\Features\SupportAuthorization\BaseAuthorize;

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class SupportJson extends ComponentHook
{
    public static function provide()
    {
        on('call', function ($component, $method, $params, $context, $returnEarly, $metadata, $index) {
            if (! static::isJsonMethod($component, $method)) return;

            // This listener runs the method itself, before the regular "call" hooks
            // would authorize it, so run any authorization checks first...
            $component->getAttributes()
                ->filter(fn ($i) => $i instanceof BaseAuthorize)
                ->filter(fn ($i) => $i->getName() === $method)
                ->filter(fn ($i) => $i->getLevel() === AttributeLevel::METHOD)
                ->each(fn ($i) => $i->call($params));

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
            } catch (\Throwable $e) {
                $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

                $existingMeta = $context->effects['returnsMeta'] ?? [];
                $existingMeta[$index] = ['status' => $status];
                $context->addEffect('returnsMeta', $existingMeta);

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
