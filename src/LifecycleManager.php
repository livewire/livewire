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

    public function mount($params = [], $passParamsToProps = true)
    {
        if ($passParamsToProps) {
            $matchingProps = array_intersect_key($params, $this->instance->getPublicPropertiesDefinedBySubClass());

            foreach ($matchingProps as $property => $mountValue) {
                $reflected = new ReflectionProperty($this->instance, $property);
                $typeHint = Reflector::getParameterClassName($reflected);

                if (! $typeHint || is_a($mountValue, $typeHint)) {
                    $this->instance->{$property} = $mountValue;
                }
            }

            // If we don't have a mount method, and all the params matched props, just stop
            if (! method_exists($this->instance, 'mount') && count($params) === count($matchingProps)) {
                return $this;
            }
        }

        try {
            if (! method_exists($this->instance, 'mount') && count($params) > 0) {
                throw new MountMethodMissingException($this->instance->getName());
            }

            if (! method_exists($this->instance, 'mount')) return $this;

            ImplicitlyBoundMethod::call(app(), [$this->instance, 'mount'], $params);
        } catch (ValidationException $e) {
            Livewire::dispatch('failed-validation', $e->validator);

            $this->instance->setErrorBag($e->validator->errors());
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
