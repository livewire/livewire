<?php

namespace Tests\Browser\SyncHistory;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use \Sushi\Sushi;

    protected $rows = [
        [
            'id' => 1,
            'username' => '@danielcoulbourne',
        ],
        [
            'id' => 2,
            'username' => '@calebporzio',
        ],
        [
            'id' => 3,
            'username' => '@inxilpro',
        ],
    ];
}
