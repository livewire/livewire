<?php

namespace LegacyTests\Browser\StringNormalization;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Sushi\Sushi;

class Model extends BaseModel
{
    use Sushi;

    protected $rows = [
        [
            'id' => 1,
            'name' => 'â'
        ]
    ];
}
