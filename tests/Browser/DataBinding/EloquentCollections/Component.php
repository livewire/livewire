<?php

namespace Tests\Browser\DataBinding\EloquentCollections;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $authors;

    protected $rules = [
        'authors.*.name' => '',
        'authors.*.email' => '',
        'authors.*.posts.*.title' => '',
        'authors.*.posts.*.comments.*.comment' => '',
        'authors.*.posts.*.comments.*.author.name' => '',
    ];

    public function mount()
    {
        $this->authors = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->get();
    }

    public function render()
    {
        return
        <<<'HTML'
<div>
    <button type="button" wire:click="$refresh">Refresh</button>
    @foreach($authors as $authorKey => $author)
        <div>
            <div>
                Author Name<input dusk='author-{{ $author->id }}-name' wire:model='authors.{{ $authorKey }}.name' />
                Author Email<input dusk='author-{{ $author->id }}-email' wire:model='authors.{{ $authorKey }}.email' />
            </div>

            <div>
                @foreach($author->posts as $postKey => $post)
                    <div>
                        Post Title<input dusk='post-{{ $post->id }}-title' wire:model="authors.{{ $authorKey }}.posts.{{ $postKey }}.title" />

                        <div>
                            @foreach($post->comments as $commentKey => $comment)
                                <div>
                                    Comment Comment<input
                                        dusk='comment-{{ $comment->id }}-comment'
                                        wire:model="authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment"
                                        />
                                    Commment Author Name<input
                                        dusk='comment-{{ $comment->id }}-comment'
                                        wire:model="authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name"
                                        />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
HTML;
    }
}
