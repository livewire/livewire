<?php

namespace Livewire\Features\SupportFormObjects;

use Livewire\Features\SupportValidation\HandlesValidation;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\MessageBag;
use function Livewire\invade;
use function Livewire\wrap;
use Illuminate\Support\Arr;
use Livewire\Drawer\Utils;
use Livewire\Component;
use Livewire\Concerns\InteractsWithProperties;
use Livewire\Features\SupportAttributes\AttributeCollection;
use Livewire\Features\SupportVirtualProperties\HandlesVirtualProperties;

class Form implements Arrayable
{
    use HandlesValidation {
        validate as parentValidate;
        validateOnly as parentValidateOnly;
    }

    use InteractsWithProperties;
    use HandlesVirtualProperties;

    protected $booted = false;

    function __construct(
        protected Component $component,
        protected $propertyName
    ) {}

    public function getComponent() { return $this->component; }
    public function getPropertyName() { return $this->propertyName; }

    // However a form comes into existence — a declared property, a #[Virtual]
    // method, a reset — its attributes reach the component here...
    public function bootIfNotBooted()
    {
        if ($this->booted) return;

        $this->booted = true;

        // A form replacing another at this path takes its attributes with it;
        // otherwise hooks keep firing against a discarded instance...
        $this->component->forgetAttributesWhere(function ($attribute) {
            $subTarget = $attribute->getSubTarget();

            return $subTarget instanceof Form
                && $subTarget !== $this
                && $subTarget->getPropertyName() === $this->propertyName;
        });

        $attributes = AttributeCollection::fromComponent($this->component, $this, $this->propertyName.'.');

        $this->component->mergeOutsideAttributes($attributes);

        // Not left to the component's boot hook — a form built after it (a
        // reset mid-request) still needs its rules registered...
        $attributes->each(fn ($attribute) => $attribute->callBoot());

        wrap($this)->boot();
    }

    public function validate($rules = null, $messages = [], $attributes = [])
    {
        try {
            return $this->parentValidate($rules, $messages, $attributes);
        } catch (ValidationException $e) {
            invade($e->validator)->messages = $this->prefixErrorBag(invade($e->validator)->messages);
            invade($e->validator)->failedRules = $this->prefixArray(invade($e->validator)->failedRules);

            throw $e;
        }
    }

    public function validateOnly($field, $rules = null, $messages = [], $attributes = [], $dataOverrides = [])
    {
        try {
            return $this->parentValidateOnly($field, $rules, $messages, $attributes, $dataOverrides);
        } catch (ValidationException $e) {
            invade($e->validator)->messages = $this->prefixErrorBag(invade($e->validator)->messages)->merge(
                $this->getComponent()->errorBagExcept($this->getPropertyName() . '.' . $field)
            );

            invade($e->validator)->failedRules = $this->prefixArray(invade($e->validator)->failedRules);

            throw $e;
        }
    }

    protected function runSubValidators()
    {
        // This form object IS the sub-validator.
        // Let's skip it...
    }

    protected function prefixErrorBag($bag)
    {
        $raw = $bag->toArray();

        $raw = Arr::prependKeysWith($raw, $this->getPropertyName().'.');

        return new MessageBag($raw);
    }

    protected function prefixArray($array)
    {
        return Arr::prependKeysWith($array, $this->getPropertyName().'.');
    }

    public function addError($key, $message)
    {
        $this->component->addError($this->propertyName . '.' . $key, $message);
    }

    public function resetErrorBag($field = null)
    {
        $fields = (array) $field;

        foreach ($fields as $idx => $field) {
            $fields[$idx] = $this->propertyName . '.' . $field;
        }

        $this->getComponent()->resetErrorBag($fields);
    }

    public function all()
    {
        return $this->toArray();
    }

    public function reset(...$properties)
    {
        $properties = count($properties) && is_array($properties[0])
            ? $properties[0]
            : $properties;

        if (empty($properties)) $properties = array_keys($this->all());

        $freshInstance = new static($this->getComponent(), $this->getPropertyName());

        foreach ($properties as $property) {
            data_set($this, $property, data_get($freshInstance, $property));
        }
    }

    public function toArray()
    {
        return Utils::getPublicProperties($this);
    }
}
