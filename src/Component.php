<?php

namespace Livewire;

use BadMethodCallException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Traits\Macroable;
use Livewire\Concerns\InteractsWithProperties;
use Livewire\Exceptions\PropertyNotFoundException;
use Livewire\Features\SupportAttributes\HandlesAttributes;
use Livewire\Features\SupportDisablingBackButtonCache\HandlesDisablingBackButtonCache;
use Livewire\Features\SupportEvents\HandlesEvents;
use Livewire\Features\SupportJsEvaluation\HandlesJsEvaluation;
use Livewire\Features\SupportPageComponents\HandlesPageComponents;
use Livewire\Features\SupportRedirects\HandlesRedirects;
use Livewire\Features\SupportStreaming\HandlesStreaming;
use Livewire\Features\SupportValidation\HandlesValidation;

abstract class Component
{
    use AuthorizesRequests;
    use HandlesAttributes;
    use HandlesDisablingBackButtonCache;
    use HandlesEvents;
    use HandlesJsEvaluation;
    use HandlesPageComponents;
    use HandlesRedirects;
    use HandlesStreaming;
    use HandlesValidation;
    use InteractsWithProperties;
    use Macroable { __call as macroCall; }

    protected $__id;

    protected $__name;

    public function id()
    {
        return $this->getId();
    }

    public function setId($id)
    {
        $this->__id = $id;
    }

    public function getId()
    {
        return $this->__id;
    }

    public function setName($name)
    {
        $this->__name = $name;
    }

    public function getName()
    {
        return $this->__name;
    }

    public function skipRender($html = null)
    {
        store($this)->set('skipRender', $html ?: true);
    }

    public function skipMount()
    {
        store($this)->set('skipMount', true);
    }

    public function skipHydrate()
    {
        store($this)->set('skipHydrate', true);
    }

    public function __isset($property)
    {
        try {
            $value = $this->__get($property);

            if (isset($value)) {
                return true;
            }
        } catch (PropertyNotFoundException $ex) {
        }

        return false;
    }

    public function __get($property)
    {
        $value = 'noneset';

        $returnValue = function ($newValue) use (&$value) {
            $value = $newValue;
        };

        $finish = trigger('__get', $this, $property, $returnValue);

        $value = $finish($value);

        if ($value === 'noneset') {
            throw new PropertyNotFoundException($property, $this->getName());
        }

        return $value;
    }

    public function __unset($property)
    {
        trigger('__unset', $this, $property);
    }

    public function __call($method, $params)
    {
        $value = 'noneset';

        $returnValue = function ($newValue) use (&$value) {
            $value = $newValue;
        };

        $finish = trigger('__call', $this, $method, $params, $returnValue);

        $value = $finish($value);

        if ($value !== 'noneset') {
            return $value;
        }

        if (static::hasMacro($method)) {
            return $this->macroCall($method, $params);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
