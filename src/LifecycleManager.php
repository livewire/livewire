<?php

namespace Livewire;

use Illuminate\Support\Reflector;
use Livewire\ImplicitlyBoundMethod;
use Illuminate\Validation\ValidationException;
use Livewire\Exceptions\MountMethodMissingException;
use ReflectionProperty;

class LifecycleManager
{
    public $request;
    public $instance;
    public $response;

    public static function fromSubsequentRequest($payload)
    {
        return tap(new static, function ($instance) use ($payload) {
            $instance->request = new Request($payload);
            $instance->instance = app('livewire')->getInstance($instance->request->name(), $instance->request->id());
        });
    }

    public static function fromInitialRequest($name, $id)
    {
        return tap(new static, function ($instance) use ($name, $id) {
            $instance->instance = app('livewire')->getInstance($name, $id);
            $instance->request = new Request([
                'fingerprint' => ['id' => $id, 'name' => $name, 'locale' => app()->getLocale()],
                'updates' => [],
                'serverMemo' => [],
            ]);
        });
    }

    public static function fromInitialInstance($component)
    {
        return tap(new static, function ($instance) use ($component) {
            $instance->instance = $component;
            $instance->request = new Request([
                'fingerprint' => ['id' => $component->id, 'name' => $component->getName(), 'locale' => app()->getLocale()],
                'updates' => [],
                'serverMemo' => [],
            ]);
        });
    }

    public function hydrate()
    {
        Livewire::hydrate($this->instance, $this->request);

        $this->instance->hydrate();

        return $this;
    }

    public function initialHydrate()
    {
        Livewire::initialHydrate($this->instance, $this->request);

        return $this;
    }

    public function mount($params = [])
    {
        $matchingProps = collect(array_intersect_key($params, $this->instance->getPublicPropertiesDefinedBySubClass()))
            ->filter(function ($mountValue, $property) {
                $typeHint = Reflector::getParameterClassName(
                    new ReflectionProperty($this->instance, $property)
                );

                return ! $typeHint || is_a($mountValue, $typeHint);
            })
            ->each(function ($mountValue, $property) {
                $this->instance->{$property} = $mountValue;
            });

        if (method_exists($this->instance, 'mount')) {
            try {
                ImplicitlyBoundMethod::call(app(), [$this->instance, 'mount'], $params);
            } catch (ValidationException $e) {
                Livewire::dispatch('failed-validation', $e->validator);

                $this->instance->setErrorBag($e->validator->errors());
            }
        } elseif ($matchingProps->count() !== count($params)) {
            throw new MountMethodMissingException($this->instance->getName());
        }

        return $this;
    }

    public function renderToView()
    {
        $this->instance->renderToView();

        return $this;
    }

    public function initialDehydrate()
    {
        $this->response = Response::fromRequest($this->request);

        Livewire::initialDehydrate($this->instance, $this->response);

        return $this;
    }

    public function dehydrate()
    {
        $this->response = Response::fromRequest($this->request);

        Livewire::dehydrate($this->instance, $this->response);

        return $this;
    }

    public function toInitialResponse()
    {
        $this->response->embedThyselfInHtml();

        app('livewire')->dispatch('mounted', $this->response);

        return $this->response->toInitialResponse();
    }

    public function toSubsequentResponse()
    {
        return $this->response->toSubsequentResponse();
    }
}
