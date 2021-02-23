<?php

namespace Tests\Browser\DataBinding\EagerLoading;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $posts;

    public $comments;

    public function mount()
    {
        $this->posts = Post::with('comments')->get();
        $this->comments = Comment::all();
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
