<?php

namespace Tests\Browser\DataBinding\EagerLoading;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $posts;

    public function mount()
    {
        $this->posts = Post::with('comments')->get();
    }

    public function isPostsCommentsRelationLoaded()
    {
        return $this->posts->every(function($post) {
            return $post->relationLoaded('comments');
        });
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <div dusk="comments-relation-loaded">
                {{ $this->isPostsCommentsRelationLoaded() ? 'true' : 'false' }}
            </div>

            <button dusk="refresh-server" type="button" wire:click="$refresh">Refresh Server</button>
        </div>
        HTML;
    }
}
