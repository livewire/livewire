<?php

namespace Tests\Browser\DataBinding\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function something_works()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->tinker()
                /**
                 * Standard select.
                 */
                // ->assertDontSeeIn('@single.output', 'bar')
                // ->waitForLivewire()->select('@single.input', 'bar')
                // ->assertSelected('@single.input', 'bar')
                // ->assertSeeIn('@single.output', 'bar')
                ;
        });
    }
}

class Author extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'name' => 'Bob', 'email' => 'bob@bob.com'],
        ['id' => 2, 'name' => 'John', 'email' => 'john@john.com']
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Post extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1],
        ['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1],
        ['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
