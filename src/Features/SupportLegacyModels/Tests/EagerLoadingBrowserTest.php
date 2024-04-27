<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Illuminate\Database\Eloquent\Model;
use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;
use Livewire\Component as BaseComponent;
use Sushi\Sushi;

class EagerLoadingBrowserTest extends TestCase
{
    use Concerns\EnableLegacyModels;

    public function test_it_restores_eloquent_colletion_eager_loaded_relations_on_hydrate()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, EagerLoadComponent::class)
                    ->assertSeeIn('@posts-comments-relation-loaded', 'true')
                    ->waitForLivewire()->click('@refresh-server')
                    ->assertSeeIn('@posts-comments-relation-loaded', 'true')
            ;
        });
    }

    public function test_models_without_eager_loaded_relations_are_not_affected()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, EagerLoadComponent::class)
                    ->assertSeeIn('@comments-has-no-relations', 'true')
                    ->waitForLivewire()->click('@refresh-server')
                    ->assertSeeIn('@comments-has-no-relations', 'true')
            ;
        });
    }
}

class EagerLoadComponent extends BaseComponent
{
    public $posts;

    public $comments;

    public function mount()
    {
        $this->posts = EagerLoadPost::with('comments')->get();
        $this->comments = EagerLoadComment::all();
    }

    public function postsCommentsRelationIsLoaded()
    {
        return $this->posts->every(function ($post) {
            return $post->relationLoaded('comments');
        });
    }

    public function commentsHaveNoRelations()
    {
        return $this->comments->every(function ($comments) {
            return $comments->getRelations() === [];
        });
    }

    public function render()
    {
        // ray($this->posts);
        return <<<'HTML'
<div>
    <div dusk="posts-comments-relation-loaded">
        {{ $this->postsCommentsRelationIsLoaded() ? 'true' : 'false' }}
    </div>

    <div dusk="comments-has-no-relations">
        {{ $this->commentsHaveNoRelations() ? 'true' : 'false' }}
    </div>

    <button dusk="refresh-server" type="button" wire:click="$refresh">Refresh Server</button>
</div>
HTML;
    }
}

class EagerLoadPost extends Model
{
    use Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'post1'],
        ['id' => 2, 'name' => 'post2'],
    ];

    public function comments()
    {
        return $this->hasMany(EagerLoadComment::class);
    }
}

class EagerLoadComment extends Model
{
    use Sushi;

    protected $rows = [
        ['comment' => 'comment1', 'eager_load_post_id' => 1],
        ['comment' => 'comment2', 'eager_load_post_id' => 1],
        ['comment' => 'comment3', 'eager_load_post_id' => 1],
        ['comment' => 'comment4', 'eager_load_post_id' => 1],
        ['comment' => 'comment5', 'eager_load_post_id' => 2],
        ['comment' => 'comment6', 'eager_load_post_id' => 2],
    ];

    public function post()
    {
        return $this->belongsTo(EagerLoadPost::class);
    }
}
