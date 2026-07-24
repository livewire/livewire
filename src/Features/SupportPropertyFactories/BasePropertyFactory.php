<?php

namespace Livewire\Features\SupportPropertyFactories;

use function Livewire\invade;
use function Livewire\store;

use Livewire\Features\SupportAttributes\Attribute;
use Livewire\Mechanisms\HandleSynths\HandleSynths;

#[\Attribute]
class BasePropertyFactory extends Attribute
{
    function boot()
    {
        $method = new \ReflectionMethod($this->component, parent::getName());

        if (! $method->hasReturnType()) {
            throw new PropertyFactoryMissingReturnTypeException(
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
        $this->handleMagicGet();
    }

    function call()
    {
        throw new CannotCallPropertyFactoryDirectlyException(
            $this->component->getName(), $this->getName(),
        );
    }

    function handleHydrate($context)
    {
        $instance = $this->evaluateFactory();

        if (array_key_exists($this->getName(), $context->snapshotData)) {
            $instance = app(HandleSynths::class)->hydrateInto(
                $instance, $context->snapshotData[$this->getName()], $context, $this->getName(),
            );
        }

        $this->setStoredValue($instance);
    }

    function handleMagicGet()
    {
        $value = store($this->component)->find(
            'propertyFactories', $this->getName(), fn () => $this->evaluateFactory(),
        );

        $this->setStoredValue($value);

        return $value;
    }

    function setStoredValue($value)
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

    protected function evaluateFactory()
    {
        return invade($this->component)->{parent::getName()}();
    }

    function getName()
    {
        return (string) str(parent::getName())->camel();
    }
}
