<?php

namespace Synthetic;

use JsonSerializable;
use Illuminate\Contracts\Support\Jsonable;

class Component implements Jsonable, JsonSerializable  {
    public $__props;

    public function validate($rules = null, $messages = [], $attributes = []) {
        return app('synthetic')->validate($this, $rules, $messages, $attributes);
    }

    public function jsonSerialize(): mixed {
        return $this->toJson();
    }

    public function toJson($options = 0)
    {
        return app('synthetic')->synthesize($this);
    }
}
