<?php

namespace Tests\Browser;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Post extends Model
{
    use Sushi;

    protected $rows = [
        ['title' => 'Post #1'],
        ['title' => 'Post #2'],
        ['title' => 'Post #3'],
        ['title' => 'Post #4'],
        ['title' => 'Post #5'],
        ['title' => 'Post #6'],
        ['title' => 'Post #7'],
        ['title' => 'Post #8'],
        ['title' => 'Post #9'],
        ['title' => 'Post #10'],
        ['title' => 'Post #11'],
        ['title' => 'Post #12'],
        ['title' => 'Post #13'],
        ['title' => 'Post #14'],
        ['title' => 'Post #15'],
        ['title' => 'Post #16'],
        ['title' => 'Post #17'],
        ['title' => 'Post #18'],
        ['title' => 'Post #19'],
        ['title' => 'Post #20'],
    ];
}
