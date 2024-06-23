<?php

namespace Livewire\Features\SupportValidation;

use function Livewire\invade;
use function Livewire\store;
use Illuminate\Contracts\Support\Arrayable;
use Livewire\Wireable;
use Livewire\Exceptions\MissingRulesException;
use Livewire\Drawer\Utils;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ViewErrorBag;
use Livewire\Form;

trait HandlesValidation
{
    protected $withValidatorCallback;

    protected $rulesFromOutside = [];
    protected $messagesFromOutside = [];
    protected $validationAttributesFromOutside = [];

    public function addRulesFromOutside($rules)
    {
        $this->rulesFromOutside[] = $rules;
    }

    public function addMessagesFromOutside($messages)
    {
        $this->messagesFromOutside[] = $messages;
    }

    public function addValidationAttributesFromOutside($validationAttributes)
    {
        $this->validationAttributesFromOutside[] = $validationAttributes;
    }

    public function getErrorBag()
    {
        if (! store($this)->has('errorBag')) {
            $previouslySharedErrors = app('view')->getShared()['errors'] ?? new ViewErrorBag;
            $this->setErrorBag($previouslySharedErrors->getMessages());
        }

        return store($this)->get('errorBag');
    }

    public function addError($name, $message)
    {
        return $this->getErrorBag()->add($name, $message);
    }

    public function setErrorBag($bag)
    {
        return store($this)->set('errorBag', $bag instanceof MessageBag
            ? $bag
            : new MessageBag($bag)
        );
    }

    public function resetErrorBag($field = null)
    {
        $fields = (array) $field;

        if (empty($fields)) {
            $errorBag = new MessageBag;

            $this->setErrorBag($errorBag);

            return $errorBag;
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

    public function getRules()
    {
        $rulesFromComponent = [];

        if (method_exists($this, 'rules')) $rulesFromComponent = $this->rules();
        else if (property_exists($this, 'rules')) $rulesFromComponent = $this->rules;

        $rulesFromOutside = array_merge_recursive(
            ...array_map(
                fn($i) => value($i),
                $this->rulesFromOutside
            )
        );

        return array_merge($rulesFromComponent, $rulesFromOutside);
    }

    protected function getMessages()
    {
        $messages = [];

        if (method_exists($this, 'messages')) $messages = $this->messages();
        elseif (property_exists($this, 'messages')) $messages = $this->messages;

        $messagesFromOutside = array_merge(
            ...array_map(
                fn($i) => value($i),
                $this->messagesFromOutside
            )
        );

        return array_merge($messages, $messagesFromOutside);
    }

    protected function getValidationAttributes()
    {
        $validationAttributes = [];

        if (method_exists($this, 'validationAttributes')) $validationAttributes = $this->validationAttributes();
        elseif (property_exists($this, 'validationAttributes')) $validationAttributes = $this->validationAttributes;

        $validationAttributesFromOutside = array_merge(
            ...array_map(
                fn($i) => value($i),
                $this->validationAttributesFromOutside
            )
        );

        return array_merge($validationAttributes, $validationAttributesFromOutside);
    }

    protected function getValidationCustomValues()
    {
        if (method_exists($this, 'validationCustomValues')) return $this->validationCustomValues();
        if (property_exists($this, 'validationCustomValues')) return $this->validationCustomValues;

        return [];
    }

    public function rulesForModel($name)
    {
        if (empty($this->getRules())) return collect();

        return collect($this->getRules())
            ->filter(function ($value, $key) use ($name) {
                return Utils::beforeFirstDot($key) === $name;
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

    protected function checkRuleMatchesProperty($rules, $data)
    {
        collect($rules)
            ->keys()
            ->each(function($ruleKey) use ($data) {
                throw_unless(
                    array_key_exists(Utils::beforeFirstDot($ruleKey), $data),
                    new \Exception('No property found for validation: ['.$ruleKey.']')
                );
            });
    }

    public function validate($rules = null, $messages = [], $attributes = [])
    {
        $isUsingGlobalRules = is_null($rules);

        [$rules, $messages, $attributes] = $this->providedOrGlobalRulesMessagesAndAttributes($rules, $messages, $attributes);

        $data = $this->prepareForValidation(
            $this->getDataForValidation($rules)
        );

        $this->checkRuleMatchesProperty($rules, $data);

        $ruleKeysToShorten = $this->getModelAttributeRuleKeysToShorten($data, $rules);

        $data = $this->unwrapDataForValidation($data);

        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($this->withValidatorCallback) {
            call_user_func($this->withValidatorCallback, $validator);

            $this->withValidatorCallback = null;
        }

        $this->shortenModelAttributesInsideValidator($ruleKeysToShorten, $validator);

        $customValues = $this->getValidationCustomValues();

        if (! empty($customValues)) {
            $validator->addCustomValues($customValues);
        }

        if ($this->isRootComponent() && $isUsingGlobalRules) {
            $validatedData = $this->withFormObjectValidators($validator, fn () => $validator->validate(), fn ($form) => $form->validate());
        } else {
            $validatedData = $validator->validate();
        }

        $this->resetErrorBag();

        return $validatedData;
    }

    protected function isRootComponent()
    {
        // Because this trait is used for form objects as well...
        return $this instanceof \Livewire\Component;
    }

    protected function withFormObjectValidators($validator, $validateSelf, $validateForm)
    {
        $cumulativeErrors = new MessageBag;
        $cumulativeData = [];
        $formExceptions = [];

        // First, run sub-validators...
        foreach ($this->getFormObjects() as $form) {
            try {
                // Only run sub-validator if the sub-validator has rules...
                if (filled($form->getRules())) {
                    $cumulativeData = array_merge($cumulativeData, $validateForm($form));
                }
            } catch (ValidationException $e) {
                $cumulativeErrors->merge($e->validator->errors());

                $formExceptions[] = $e;
            }
        }

        // Now run main validator...
        try {
            $cumulativeData = array_merge($cumulativeData, $validateSelf());
        } catch (ValidationException $e) {
            // If the main validator has errors, merge them with subs and rethrow...
            $e->validator->errors()->merge($cumulativeErrors);

            throw $e;
        }

        // If main validation passed, go through other sub-validation exceptions
        // and throw the first one with the cumulative messages...
        foreach ($formExceptions as $e) {
            $e->validator->errors()->merge($cumulativeErrors->unique());

            throw $e;
        }

        // All validation has passed, we can return the data...
        return $cumulativeData;
    }

    public function validateOnly($field, $rules = null, $messages = [], $attributes = [], $dataOverrides = [])
    {
        $property = (string) str($field)->before('.');

        // If validating a field in a form object, defer validation to that form object...
        if (
            $this->isRootComponent()
            && ($form = $this->all()[$property] ?? false) instanceof Form
        ) {
            $stripPrefix = (string) str($field)->after('.');
            return $form->validateOnly($stripPrefix, $rules, $messages, $attributes, $dataOverrides);
        }

        $isUsingGlobalRules = is_null($rules);

        [$rules, $messages, $attributes] = $this->providedOrGlobalRulesMessagesAndAttributes($rules, $messages, $attributes);

        // Loop through rules and swap any wildcard '*' with keys from field, then filter down to only
        // rules that match the field, but return the rules without wildcard characters replaced,
        // so that custom attributes and messages still work as they need wildcards to work.
        $rulesForField = collect($rules)
            ->filter(function($value, $rule) use ($field) {
                if(! str($field)->is($rule)) {
                    return false;
                }

                $fieldArray = str($field)->explode('.');
                $ruleArray = str($rule)->explode('.');

                for($i = 0; $i < count($fieldArray); $i++) {
                    if(isset($ruleArray[$i]) && $ruleArray[$i] === '*') {
                        $ruleArray[$i] = $fieldArray[$i];
                    }
                }

                $rule = $ruleArray->join('.');

                return $field === $rule;
            });

        $ruleForField = $rulesForField->keys()->first();

        $rulesForField = $rulesForField->toArray();

        $ruleKeysForField = array_keys($rulesForField);

        $data = array_merge($this->getDataForValidation($rules), $dataOverrides);

        $data = $this->prepareForValidation($data);

        $this->checkRuleMatchesProperty($rules, $data);

        $ruleKeysToShorten = $this->getModelAttributeRuleKeysToShorten($data, $rules);

        $data = $this->unwrapDataForValidation($data);

        // If a matching rule is found, then filter collections down to keys specified in the field,
        // while leaving all other data intact. If a key isn't specified and instead there is a
        // wildcard '*' then leave that whole collection intact. This ensures that any rules
        // that depend on other fields/ properties still work.
        if ($ruleForField) {
            $ruleArray = str($ruleForField)->explode('.');
            $fieldArray = str($field)->explode('.');

            $data = $this->filterCollectionDataDownToSpecificKeys($data, $ruleArray, $fieldArray);
        }

        $validator = Validator::make($data, $rulesForField, $messages, $attributes);

        if ($this->withValidatorCallback) {
            call_user_func($this->withValidatorCallback, $validator);

            $this->withValidatorCallback = null;
        }

        $this->shortenModelAttributesInsideValidator($ruleKeysToShorten, $validator);

        $customValues = $this->getValidationCustomValues();
        if (!empty($customValues)) {
            $validator->addCustomValues($customValues);
        }

        try {
            $result = $validator->validate();
        } catch (ValidationException $e) {
            $messages = $e->validator->getMessageBag();

            invade($e->validator)->messages = $messages->merge(
                $this->errorBagExcept($ruleKeysForField)
            );

            throw $e;
        }

        $this->resetErrorBag($ruleKeysForField);

        return $result;
    }

    protected function filterCollectionDataDownToSpecificKeys($data, $ruleKeys, $fieldKeys)
    {

        // Filter data down to specified keys in collections, but leave all other data intact
        if (count($ruleKeys)) {
            $ruleKey = $ruleKeys->shift();
            $fieldKey = $fieldKeys->shift();

            if ($fieldKey == '*') {
                // If the specified field has a '*', then loop through the collection and keep the whole collection intact.
                foreach ($data as $key => $value) {
                    $data[$key] = $this->filterCollectionDataDownToSpecificKeys($value, $ruleKeys, $fieldKeys);
                }
            } else {
                // Otherwise filter collection down to a specific key
                $keyData = data_get($data, $fieldKey, null);

                if ($ruleKey == '*') {
                    $data = [];
                }

                data_set($data, $fieldKey, $this->filterCollectionDataDownToSpecificKeys($keyData, $ruleKeys, $fieldKeys));
            }
        }

        return $data;
    }

    protected function getModelAttributeRuleKeysToShorten($data, $rules)
    {
        // If a model ($foo) is a property, and the validation rule is
        // "foo.bar", then set the attribute to just "bar", so that
        // the validation message is shortened and more readable.

        $toShorten = [];

        foreach ($rules as $key => $value) {
            $propertyName = Utils::beforeFirstDot($key);

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
                $validator->addCustomAttributes([$key => $validator->getDisplayableAttribute(Utils::afterFirstDot($key))]);
            }
        }
    }

    protected function providedOrGlobalRulesMessagesAndAttributes($rules, $messages, $attributes)
    {
        $rules = is_null($rules) ? $this->getRules() : $rules;

        // Before we warn the user about not providing validation rules,
        // Let's make sure there are no form objects that contain them...
        $allRules = $rules;

        if ($this->isRootComponent()) {
            foreach ($this->getFormObjects() as $form) {
                $allRules = array_merge($allRules, $form->getRules());
            }
        }

        throw_if(empty($allRules), new MissingRulesException($this));

        $messages = empty($messages) ? $this->getMessages() : $messages;
        $attributes = empty($attributes) ? $this->getValidationAttributes() : $attributes;

        return [$rules, $messages, $attributes];
    }

    protected function getDataForValidation($rules)
    {
        return Utils::getPublicPropertiesDefinedOnSubclass($this);
    }

    protected function unwrapDataForValidation($data)
    {
        return collect($data)->map(function ($value) {
            // @todo: this logic should be contained within "SupportWireables"...
            if ($value instanceof Wireable) return $value->toLivewire();
            else if ($value instanceof Arrayable) return $value->toArray();

            return $value;
        })->all();
    }

    protected function prepareForValidation($attributes)
    {
        return $attributes;
    }
}
