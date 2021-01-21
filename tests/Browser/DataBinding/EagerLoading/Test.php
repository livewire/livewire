<?php

namespace Tests\Browser\DataBinding\EagerLoading;

use Illuminate\Database\Eloquent\Model;
use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function it_restores_eloquent_colletion_eager_loaded_relations_on_hydrate()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                    ->assertSeeIn('@posts-comments-relation-loaded', 'true')
                    ->waitForLivewire()->click('@refresh-server')
                    ->assertSeeIn('@posts-comments-relation-loaded', 'true')
                    ;
        });
    }

    /** @test */
    public function models_without_eager_loaded_relations_are_not_affected()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                    ->assertSeeIn('@comments-has-no-relations', 'true')
                    ->waitForLivewire()->click('@refresh-server')
                    ->assertSeeIn('@comments-has-no-relations', 'true')
                    ;
        });
    }
}

class Post extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'post1'],
        ['id' => 2, 'name' => 'post2'],
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    use Sushi;

    protected $rows = [
        ['comment' => 'comment1', 'post_id' => 1],
        ['comment' => 'comment2', 'post_id' => 1],
        ['comment' => 'comment3', 'post_id' => 1],
        ['comment' => 'comment4', 'post_id' => 1],
        ['comment' => 'comment5', 'post_id' => 2],
        ['comment' => 'comment6', 'post_id' => 2],
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
