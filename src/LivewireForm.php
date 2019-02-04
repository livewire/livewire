<?php

namespace Livewire;

use Illuminate\Support\Facades\Validator;

class LivewireForm {
    public $validator;
    public $errors;
    protected $rules;
    protected $values;
    protected $needsInputRefresh = false;

    public function defaults($values)
    {
        foreach ($values as $key => $value) {
            $this->values[$key] = $value;
        }

        $this->defaultValues =  $this->values;
    }

    public function needsInputRefresh()
    {
        return !! $this->needsInputRefresh;
    }
    public function setForRefresh()
    {
        $this->needsInputRefresh = true;
    }

    public function refreshed()
    {
        $this->needsInputRefresh = false;
    }

    public function __construct($rules)
    {
        $this->rules = $rules;

        $this->values = array_fill_keys(array_keys($this->rules), null);

        $this->updateValidator();
    }

    public function fill($attributes)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, array_keys($this->values))) {
                $this->values[$key] = $value;
            }
        }

        $this->updateValidator();
        $this->setForRefresh();
    }

    public function clear()
    {
        $this->values = $this->defaultValues;

        $this->setForRefresh();
    }

    public function updateValue($name, $value)
    {
        throw_unless(in_array($name, array_keys($this->values)), new \Exception('value is not defined'));

        $this->values[$name] = $value;

        $this->updateValidator();
    }

    public function updateValidator()
    {
        $this->validator = Validator::make($this->values, $this->rules);
        $this->errors = $this->validator->errors();
    }

    public function __get($attribute)
    {
        return $this->values[$attribute];
    }

    public function __sleep()
    {
        $properties = array_map(function ($prop) {
            return $prop->getName();
        }, (new \ReflectionObject($this))->getProperties());

        return array_diff($properties, ['validator']);
    }

    public function __wakeup()
    {
        $this->updateValidator();
    }
}
