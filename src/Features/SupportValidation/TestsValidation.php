<?php

namespace Livewire\Features\SupportValidation;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use function Livewire\store;
use PHPUnit\Framework\Assert as PHPUnit;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;

trait TestsValidation
{
    function errors()
    {
        $validator = store($this->target)->get('testing.validator');

        if ($validator) return $validator->errors();

        $errors = store($this->target)->get('testing.errors');

        if ($errors) return $errors;

        return new MessageBag;
    }

    function failedRules()
    {
        $validator = store($this->target)->get('testing.validator');

        return $validator ? $validator->failed() : [];
    }

    public function assertHasErrors($keys = [])
    {
        $errors = $this->errors();

        PHPUnit::assertTrue($errors->isNotEmpty(), 'Component has no errors.');

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                $this->makeErrorAssertion($value);
            } else {
                $this->makeErrorAssertion($key, $value);
            }
        }

        return $this;
    }

    protected function makeErrorAssertion($key = null, $value = null) {
        $errors = $this->errors();

        $messages = $errors->get($key);

        $failed = $this->failedRules() ?: [];
        $failedRules = array_keys(Arr::get($failed, $key, []));
        $failedRules = array_map(function (string $rule) {
            if (is_a($rule, ValidationRule::class, true) || is_a($rule, Rule::class, true)) {
                return $rule;
            }

            return Str::snake($rule);
        }, $failedRules);

        PHPUnit::assertTrue($errors->isNotEmpty(), 'Component has no errors.');

        if (is_null($value)) {
            PHPUnit::assertTrue($errors->has($key), "Component missing error: $key");
        } elseif ($value instanceof Closure) {
            PHPUnit::assertTrue($value($failedRules, $messages));
        } elseif (is_array($value)) {
            foreach ((array) $value as $ruleOrMessage) {
                $this->assertErrorMatchesRuleOrMessage($failedRules, $messages, $key, $ruleOrMessage);
            }
        } else {
            $this->assertErrorMatchesRuleOrMessage($failedRules, $messages, $key, $value);
        }

        return $this;
    }

    protected function assertErrorMatchesRuleOrMessage($rules, $messages, $key, $ruleOrMessage)
    {
        if (Str::contains($ruleOrMessage, ':')){
            $ruleOrMessage = Str::before($ruleOrMessage, ':');
        }

        if (in_array($ruleOrMessage, $rules)) {
            PHPUnit::assertTrue(true);

            return;
        }

        // If the provided rule/message isn't a failed rule, let's check to see if it's a message...
        PHPUnit::assertContains($ruleOrMessage, $messages, "Component has no matching failed rule or error message [{$ruleOrMessage}] for [{$key}] attribute.");
    }


    public function assertHasNoErrors($keys = [])
    {
        $errors = $this->errors();

        if (empty($keys)) {
            PHPUnit::assertTrue($errors->isEmpty(), 'Component has errors: "'.implode('", "', $errors->keys()).'"');

            return $this;
        }

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertFalse($errors->has($value), "Component has error: $value");
            } else {
                $failed = $this->failedRules() ?: [];
                $rules = array_keys(Arr::get($failed, $key, []));

                foreach ((array) $value as $rule) {
                    if (Str::contains($rule, ':')){
                        $rule = Str::before($rule, ':');
                    }

                    PHPUnit::assertNotContains(Str::studly($rule), $rules, "Component has [{$rule}] errors for [{$key}] attribute.");
                }
            }
        }

        return $this;
    }
}
