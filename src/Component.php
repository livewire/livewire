<?php

namespace Livewire;

use Livewire\Features\SupportDisablingBackButtonCache\HandlesDisablingBackButtonCache;
use Livewire\Features\SupportPageComponents\HandlesPageComponents;
use Livewire\Features\SupportReleaseTokens\HandlesReleaseTokens;
use Livewire\Features\SupportJsEvaluation\HandlesJsEvaluation;
use Livewire\Features\SupportAttributes\HandlesAttributes;
use Livewire\Features\SupportValidation\HandlesValidation;
use Livewire\Features\SupportStreaming\HandlesStreaming;
use Livewire\Features\SupportRedirects\HandlesRedirects;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Features\SupportEvents\HandlesEvents;
use Livewire\Exceptions\PropertyNotFoundException;
use Livewire\Concerns\InteractsWithProperties;
use Illuminate\Support\Traits\Macroable;
use BadMethodCallException;
use Livewire\Features\SupportFormObjects\HandlesFormObjects;

abstract class Component
{
    use Macroable { __call as macroCall; }

    use AuthorizesRequests;
    use InteractsWithProperties;
    use HandlesEvents;
    use HandlesRedirects;
    use HandlesStreaming;
    use HandlesAttributes;
    use HandlesValidation;
    use HandlesFormObjects;
    use HandlesJsEvaluation;
    use HandlesReleaseTokens;
    use HandlesPageComponents;
    use HandlesDisablingBackButtonCache;

    protected $__id;
    protected $__name;

    function id()
    {
        return $this->getId();
    }

    function setId($id)
    {
        $this->__id = $id;
    }

    function getId()
    {
        return $this->__id;
    }

    function setName($name)
    {
        $this->__name = $name;
    }

    function getName()
    {
        return $this->__name;
    }

    function skipRender($html = null)
    {
        store($this)->set('skipRender', $html ?: true);
    }

    function skipMount()
    {
        store($this)->set('skipMount', true);
    }

    function skipHydrate()
    {
        store($this)->set('skipHydrate', true);
    }

    function __isset($property)
    {
        try {
            $value = $this->__get($property);

            if (isset($value)) {
                return true;
            }
        } catch(PropertyNotFoundException $ex) {}

        return false;
    }

    function __get($property)
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

    function __unset($property)
    {
        trigger('__unset', $this, $property);
    }

    function __call($method, $params)
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

    public function tap($callback)
    {
        $callback($this);

        return $this;
    }
}
