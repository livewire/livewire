<?php

namespace Synthetic;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait SyntheticValidation {
    function validate($data, $rules, $messages = [], $attributes = []) {
        $this->checkRuleMatchesProperty($rules, $data);

        $data = $this->unwrapDataForValidation($data);

        $validator = Validator::make($data, $rules, $messages, $attributes);

        $validatedData = $validator->validate();

        // Why is this here?
        // $this->resetErrorBag();

        return $validatedData;
    }

    protected function checkRuleMatchesProperty($rules, $data)
    {
        collect($rules)
            ->keys()
            ->each(function($ruleKey) use ($data) {
                throw_unless(
                    array_key_exists($this->beforeFirstDot($ruleKey), $data),
                    new \Exception('No property found for validation: ['.$ruleKey.']')
                );
            });
    }

    public function containsDots($subject)
    {
        return strpos($subject, '.') !== false;
    }

    public function beforeFirstDot($subject)
    {
        return head(explode('.', $subject));
    }

    public function afterFirstDot($subject) : string
    {
        return str($subject)->after('.');
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

    protected function unwrapDataForValidation($data)
    {
        return collect($data)->map(function ($value) {
            if ($value instanceof Collection || $value instanceof EloquentCollection || $value instanceof Model) return $value->toArray();

            return $value;
        })->all();
    }

    protected function shortenModelAttributesInsideValidator($ruleKeys, $validator)
    {
        foreach ($ruleKeys as $key) {
            if (str($key)->snake()->replace('_', ' ')->is($validator->getDisplayableAttribute($key))) {
                $validator->addCustomAttributes([$key => $validator->getDisplayableAttribute($this->afterFirstDot($key))]);
            }
        }
    }
}
