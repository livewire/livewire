<?php

namespace Livewire;

use Livewire\Features\SupportValidation\HandlesValidation;
use Livewire\Features\SupportTransitions\HandlesTransitions;
use Livewire\Features\SupportStreaming\HandlesStreaming;
use Livewire\Features\SupportSlots\HandlesSlots;
use Livewire\Features\SupportReleaseTokens\HandlesReleaseTokens;
use Livewire\Features\SupportRedirects\HandlesRedirects;
use Livewire\Features\SupportPageComponents\HandlesPageComponents;
use Livewire\Features\SupportJsEvaluation\HandlesJsEvaluation;
use Livewire\Features\SupportIslands\HandlesIslands;
use Livewire\Features\SupportFormObjects\HandlesFormObjects;
use Livewire\Features\SupportEvents\HandlesEvents;
use Livewire\Features\SupportHtmlAttributeForwarding\HandlesHtmlAttributeForwarding;
use Livewire\Features\SupportDisablingBackButtonCache\HandlesDisablingBackButtonCache;
use Livewire\Features\SupportAttributes\HandlesAttributes;
use Livewire\Exceptions\PropertyNotFoundException;
use Livewire\Concerns\InteractsWithProperties;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use BadMethodCallException;

abstract class Component
{
    use Macroable { __call as macroCall; }

    use AuthorizesRequests;
    use InteractsWithProperties;
    use HandlesEvents;
    use HandlesIslands;
    use HandlesRedirects;
    use HandlesTransitions;
    use HandlesStreaming;
    use HandlesAttributes;
    use HandlesValidation;
    use HandlesFormObjects;
    use HandlesJsEvaluation;
    use HandlesReleaseTokens;
    use HandlesPageComponents;
    use HandlesDisablingBackButtonCache;
    use HandlesSlots;
    use HandlesHtmlAttributeForwarding;

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

    function renderless()
    {
        $this->skipRender();
    }

    function skipRender($html = null)
    {
        if (store($this)->has('forceRender')) {
            return;
        }

        store($this)->set('skipRender', $html ?: true);
    }

    function forceRender()
    {
        store($this)->set('forceRender', true);
    }

    function skipMount()
    {
        store($this)->set('skipMount', true);
    }

    function skipHydrate()
    {
        store($this)->set('skipHydrate', true);
    }

    function hasProvidedView()
    {
        return method_exists($this, 'view');
    }

    function getProvidedView()
    {
        return $this->view();
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
