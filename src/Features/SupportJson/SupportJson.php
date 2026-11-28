<?php

namespace Livewire\Features\SupportJson;

use function Livewire\on;
use function Livewire\store;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\ComponentHook;
use Livewire\Features\SupportAttributes\AttributeLevel;
use Livewire\Features\SupportAuthorization\BaseAuthorize;

use Illuminate\Validation\ValidationException;
use Livewire\ImplicitlyBoundMethod;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class SupportJson extends ComponentHook
{
    public static function provide()
    {
        on('call', function ($component, $method, $params, $context, $returnEarly, $metadata, $index) {
            if (! static::isJsonMethod($component, $method)) return;

            // This listener runs the method itself, before the regular "call" hooks
            // would authorize it, so run any authorization checks first...
            $authorized = $component->getAttributes()
                ->filter(fn ($i) => $i instanceof BaseAuthorize)
                ->filter(fn ($i) => $i->getName() === $method)
                ->filter(fn ($i) => $i->getLevel() === AttributeLevel::METHOD)
                ->every(fn ($i) => $i->call($params));

            $component->skipRender();

            if (! $authorized) {
                $status = static::getStatusCode(
                    store($component)->get('exceptionForJsonMethod', new AuthorizationException)
                );

                $existingMeta = $context->effects['returnsMeta'] ?? [];
                $existingMeta[$index] = ['status' => $status];
                $context->addEffect('returnsMeta', $existingMeta);

                return $returnEarly(null);
            }

            try {
                $result = ImplicitlyBoundMethod::call(app(), [$component, $method], $params);

                $returnEarly($result);
            } catch (ValidationException $e) {
                // Add validation errors to returnsMeta effect keyed by action index
                $existingMeta = $context->effects['returnsMeta'] ?? [];
                $existingMeta[$index] = ['errors' => $e->errors()];
                $context->addEffect('returnsMeta', $existingMeta);

                // Return null so the returns array stays aligned
                $returnEarly(null);
            } catch (\Throwable $e) {
                $status = static::getStatusCode($e);

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

    protected static function getStatusCode(\Throwable $e)
    {
        return match (true) {
            $e instanceof HttpExceptionInterface => $e->getStatusCode(),
            $e instanceof AuthorizationException => $e->status() ?? 403,
            $e instanceof ModelNotFoundException => 404,
            default => 500
        };
    }
}
