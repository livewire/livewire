<?php

namespace Tests\Browser\SyncHistory;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $attributes = [
        'value' => null,
    ];

    public function __construct($value = 'value')
    {
        $this->attributes['value'] = "direct:{$value}";
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?? 'field';

        $this->attributes['value'] = "via-route:{$value}({$field})";

        return $this;
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        $field = $field ?? 'field';

        $this->attributes['value'] = "via-parent:{$value}({$field})";

        return $this;
    }
}
