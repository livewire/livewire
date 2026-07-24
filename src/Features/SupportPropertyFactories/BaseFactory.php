<?php

namespace Livewire\Features\SupportPropertyFactories;

use function Livewire\invade;
use function Livewire\store;

use Livewire\Features\SupportAttributes\Attribute;
use Livewire\Mechanisms\HandleComponents\VirtualProperty;
use Livewire\Mechanisms\HandleSynths\HandleSynths;

#[\Attribute]
class BaseFactory extends Attribute implements VirtualProperty
{
    function boot()
    {
        $method = new \ReflectionMethod($this->component, parent::getName());

        if (! $method->hasReturnType()) {
            throw new FactoryMissingReturnTypeException(
                $this->component->getName(), parent::getName(),
            );
        }

        if (property_exists($this->component, $this->getName())) {
            throw new \LogicException(
                'Livewire: ['.$this->getName().'] is already a declared property on component: ['.$this->component->getName().'] — a property factory can\'t share its name.'
            );
        }
    }

    function mount()
    {
        $this->virtualValue();
    }

    function call()
    {
        throw new CannotCallFactoryDirectlyException(
            $this->component->getName(), $this->getName(),
        );
    }

    function virtualValue()
    {
        $value = store($this->component)->find(
            'propertyFactories', $this->getName(), fn () => $this->evaluateFactory(),
        );

        store($this->component)->push('propertyFactories', $value, $this->getName());

        return $value;
    }

    // The factory supplies the configured instance fresh each request,
    // then its synth hydrates the client's raw state into it...
    function hydrateVirtualValue($valueOrTuple, $context)
    {
        $instance = $this->evaluateFactory();

        $instance = app(HandleSynths::class)->hydrateInto(
            $instance, $valueOrTuple, $context, $this->getName(),
        );

        $this->setVirtualValue($instance);
    }

    function setVirtualValue($value)
    {
        $type = (new \ReflectionMethod($this->component, parent::getName()))->getReturnType();

        // Client updates must land as the type the factory promised — the
        // same contract a typed public property enforces on assignment...
        if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
            $class = $type->getName();

            if ($value === null ? ! $type->allowsNull() : ! $value instanceof $class) {
                throw new \TypeError(
                    'Property factory ['.$this->getName().'] on component ['.$this->component->getName().'] must be an instance of ['.$class.'].'
                );
            }
        }

        store($this->component)->push('propertyFactories', $value, $this->getName());
    }

    // Springs back as a freshly constructed factory instance on next access...
    function unsetVirtualValue()
    {
        store($this->component)->unset('propertyFactories', $this->getName());
    }

    protected function evaluateFactory()
    {
        return invade($this->component)->{parent::getName()}();
    }

    function getName()
    {
        return (string) str(parent::getName())->camel();
    }
}
