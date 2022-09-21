<?php

namespace LegacyTests\Browser\SyncHistory;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    use \Sushi\Sushi;

    protected $rows = [
        [
            'id' => 1,
            'title' => 'Step 1',
        ],
        [
            'id' => 2,
            'title' => 'Step 2',
        ],
        [
            'id' => 3,
            'title' => 'Step 3',
        ],
    ];
}
