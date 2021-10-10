<?php

namespace Livewire\ComponentConcerns;

use function collect;
use function count;
use function explode;
use function Livewire\str;
use Livewire\ObjectPrybar;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Exceptions\MissingRulesException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait ValidatesInput
{
    protected $errorBag;

    protected $withValidatorCallback;

    public function getErrorBag()
    {
        return $this->errorBag ?? new MessageBag;
    }

    public function addError($name, $message)
    {
        return $this->getErrorBag()->add($name, $message);
    }

    public function setErrorBag($bag)
    {
        return $this->errorBag = $bag instanceof MessageBag
            ? $bag
            : new MessageBag($bag);
    }

    public function resetErrorBag($field = null)
    {
        $fields = (array) $field;

        if (empty($fields)) {
            return $this->errorBag = new MessageBag;
        }

        $this->setErrorBag(
            $this->errorBagExcept($fields)
        );
    }

    public function clearValidation($field = null)
    {
        $this->resetErrorBag($field);
    }

    public function resetValidation($field = null)
    {
        $this->resetErrorBag($field);
    }

    public function errorBagExcept($field)
    {
        $fields = (array) $field;

        return new MessageBag(
            collect($this->getErrorBag())
                ->reject(function ($messages, $messageKey) use ($fields) {
                    return collect($fields)->some(function ($field) use ($messageKey) {
                        return str($messageKey)->is($field);
                    });
                })
                ->toArray()
        );
    }

    protected function getRules()
    {
        if (method_exists($this, 'rules')) return $this->rules();
        if (property_exists($this, 'rules')) return $this->rules;

        return [];
    }

    protected function getMessages()
    {
        if (method_exists($this, 'messages')) return $this->messages();
        if (property_exists($this, 'messages')) return $this->messages;

        return [];
    }

    protected function getValidationAttributes()
    {
        if (method_exists($this, 'validationAttributes')) return $this->validationAttributes();
        if (property_exists($this, 'validationAttributes')) return $this->validationAttributes;

        return [];
    }

    public function rulesForModel($name)
    {
        if (empty($this->getRules())) return collect();

        return collect($this->getRules())
            ->filter(function ($value, $key) use ($name) {
                return $this->beforeFirstDot($key) === $name;
            });
    }

    public function hasRuleFor($dotNotatedProperty)
    {
        $propertyWithStarsInsteadOfNumbers = $this->ruleWithNumbersReplacedByStars($dotNotatedProperty);

        // If property has numeric indexes in it,
        if ($dotNotatedProperty !== $propertyWithStarsInsteadOfNumbers) {
            return collect($this->getRules())->keys()->contains($propertyWithStarsInsteadOfNumbers);
        }

        return collect($this->getRules())
            ->keys()
            ->map(function ($key) {
                return (string) str($key)->before('.*');
            })->contains($dotNotatedProperty);
    }

    public function ruleWithNumbersReplacedByStars($dotNotatedProperty)
    {
        // Convert foo.0.bar.1 -> foo.*.bar.*
        return (string) str($dotNotatedProperty)
            // Replace all numeric indexes with an array wildcard: (.0., .10., .007.) => .*.
            // In order to match overlapping numerical indexes (foo.1.2.3.4.name),
            // We need to use a positive look-behind, that's technically all the magic here.
            // For better understanding, see: https://regexr.com/5d1n3
            ->replaceMatches('/(?<=(\.))\d+\./', '*.')
            // Replace all numeric indexes at the end of the name with an array wildcard
            // (Same as the previous regex, but ran only at the end of the string)
            // For better undestanding, see: https://regexr.com/5d1n6
            ->replaceMatches('/\.\d+$/', '.*');
    }

    public function missingRuleFor($dotNotatedProperty)
    {
        return ! $this->hasRuleFor($dotNotatedProperty);
    }

    public function withValidator($callback)
    {
        $this->withValidatorCallback = $callback;

        return $this;
    }

    public function validate($rules = null, $messages = [], $attributes = [])
    {
        [$rules, $messages, $attributes] = $this->providedOrGlobalRulesMessagesAndAttributes($rules, $messages, $attributes);

        $data = $this->prepareForValidation(
            $this->getDataForValidation($rules)
        );

        $ruleKeysToShorten = $this->getModelAttributeRuleKeysToShorten($data, $rules);

        $data = $this->unwrapDataForValidation($data);

        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($this->withValidatorCallback) {
            call_user_func($this->withValidatorCallback, $validator);

            $this->withValidatorCallback = null;
        }

        $this->shortenModelAttributesInsideValidator($ruleKeysToShorten, $validator);

        $validatedData = $validator->validate();

        $this->resetErrorBag();

        return $validatedData;
    }

    public function validateOnly($field, $rules = null, $messages = [], $attributes = [])
    {
        [$rules, $messages, $attributes] = $this->providedOrGlobalRulesMessagesAndAttributes($rules, $messages, $attributes);

        // If the field is "items.0.foo", validation rules for "items.*.foo" is applied.
        // if the field is "items.0", validation rules for "items.*" and "items.*.foo" etc are applied.
        $rulesForField = collect($rules)
            ->filter(function ($rule, $fullFieldKey) use ($field) {
                return str($field)->is($fullFieldKey);
            })->keys()
            ->flatMap(function($key) use ($rules) {
                return collect($rules)->filter(function($rule, $ruleKey) use ($key) {
                    return str($ruleKey)->is($key);
                });
            })->mapWithKeys(function ($value, $key) use ($field) {
                $fieldArray = str($field)->explode('.');
                $result = str($key)->explode('.');

                $result->splice(0, $fieldArray->count(), $fieldArray->toArray());

                return [
                    $result->join('.') => $value,
                ];
            })->toArray();

        $ruleKeysForField = array_keys($rulesForField);

        $data = $this->prepareForValidation(
            $this->getDataForValidation($rules)
        );

        $ruleKeysToShorten = $this->getModelAttributeRuleKeysToShorten($data, $rules);

        $data = $this->unwrapDataForValidation($data);

        $validator = Validator::make($data, $rulesForField, $messages, $attributes);

        if ($this->withValidatorCallback) {
            call_user_func($this->withValidatorCallback, $validator);

            $this->withValidatorCallback = null;
        }

        $this->shortenModelAttributesInsideValidator($ruleKeysToShorten, $validator);

        try {
            $result = $validator->validate();
        } catch (ValidationException $e) {
            $messages = $e->validator->getMessageBag();
            $target = new ObjectPrybar($e->validator);

            $target->setProperty(
                'messages',
                $messages->merge(
                    $this->errorBagExcept($ruleKeysForField)
                )
            );

            throw $e;
        }

        $this->resetErrorBag($ruleKeysForField);

        return $result;
    }

    protected function getModelAttributeRuleKeysToShorten($data, $rules)
    {
        // If a model ($foo) is a property, and the validation rule is
        // "foo.bar", then set the attribute to just "bar", so that
        // the validation message is shortened and more readable.

        $toShorten = [];

        foreach ($rules as $key => $value) {
            $propertyName = $this->beforeFirstDot($key);

            if ($data[$propertyName] instanceof Model) {
                $toShorten[] = $key;
            }
        }

        return $toShorten;
    }

    protected function shortenModelAttributesInsideValidator($ruleKeys, $validator)
    {
        foreach ($ruleKeys as $key) {
            if (str($key)->snake()->replace('_', ' ')->is($validator->getDisplayableAttribute($key))) {
                $validator->addCustomAttributes([$key => $validator->getDisplayableAttribute($this->afterFirstDot($key))]);
            }
        }
    }

    protected function providedOrGlobalRulesMessagesAndAttributes($rules, $messages, $attributes)
    {
        $rules = is_null($rules) ? $this->getRules() : $rules;

        throw_if(empty($rules), new MissingRulesException($this::getName()));

        $messages = empty($messages) ? $this->getMessages() : $messages;
        $attributes = empty($attributes) ? $this->getValidationAttributes() : $attributes;

        return [$rules, $messages, $attributes];
    }

    protected function getDataForValidation($rules)
    {
        $properties = $this->getPublicPropertiesDefinedBySubClass();

        collect($rules)->keys()
            ->each(function ($ruleKey) use ($properties) {
                $propertyName = $this->beforeFirstDot($ruleKey);

                throw_unless(array_key_exists($propertyName, $properties), new \Exception('No property found for validation: ['.$ruleKey.']'));
            });

        return $properties;
    }

    protected function unwrapDataForValidation($data)
    {
        return collect($data)->map(function ($value) {
            if ($value instanceof Collection || $value instanceof EloquentCollection || $value instanceof Model) return $value->toArray();

            return $value;
        })->all();
    }

    protected function prepareForValidation($attributes)
    {
        return $attributes;
    }
}
