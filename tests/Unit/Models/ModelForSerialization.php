<?php

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;

class ModelForSerialization extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];
}
