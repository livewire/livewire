<?php

namespace Livewire\Features\SupportValidation;

use Closure;
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

    public function assertHasError($key, $value = null)
    {
        $errors = $this->errors();

        if (is_null($value)) {
            PHPUnit::assertTrue($errors->has($key), "Component missing error: $key");
        } elseif ($value instanceof Closure) {
            PHPUnit::assertTrue($value($errors->get($key)));
        } else {
            PHPUnit::assertContains($value, $errors->get($key));
        }

        return $this;
    }

    public function assertHasErrors($keys = [])
    {
        $errors = $this->errors();

        PHPUnit::assertTrue($errors->isNotEmpty(), 'Component has no errors.');

        $keys = (array) $keys;

        foreach ($keys as $key => $value) {
            if (is_int($key)) {
                PHPUnit::assertTrue($errors->has($value), "Component missing error: $value");
            } else {
                $failed = $this->failedRules() ?: [];
                $rules = array_keys(Arr::get($failed, $key, []));

                foreach ((array) $value as $rule) {
                    if (Str::contains($rule, ':')){
                        $rule = Str::before($rule, ':');
                    }

                    PHPUnit::assertContains(Str::studly($rule), $rules, "Component has no [{$rule}] errors for [{$key}] attribute.");
                }
            }
        }

        return $this;
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
