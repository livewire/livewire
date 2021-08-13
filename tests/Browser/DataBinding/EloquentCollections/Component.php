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

    public function save()
    {
        $this->authors->each->push();
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
                Author Name
                <input dusk='authors.{{ $authorKey }}.name' wire:model='authors.{{ $authorKey }}.name' />
                <span dusk='output.authors.{{ $authorKey }}.name'>{{ $author->name }}</span>

                Author Email
                <input dusk='authors.{{ $authorKey }}.email' wire:model='authors.{{ $authorKey }}.email' />
                <span dusk='output.authors.{{ $authorKey }}.email'>{{ $author->email }}</span>
            </div>

            <div>
                @foreach($author->posts as $postKey => $post)
                    <div>
                        Post Title
                        <input dusk='authors.{{ $authorKey }}.posts.{{ $postKey }}.title' wire:model="authors.{{ $authorKey }}.posts.{{ $postKey }}.title" />
                        <span dusk='output.authors.{{ $authorKey }}.posts.{{ $postKey }}.title'>{{ $post->title }}</span>

                        <div>
                            @foreach($post->comments as $commentKey => $comment)
                                <div>
                                    Comment Comment
                                    <input
                                        dusk='authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'
                                        wire:model="authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment"
                                        />
                                    <span dusk='output.authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'>{{ $comment->comment }}</span>

                                    Commment Author Name
                                    <input
                                        dusk='authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'
                                        wire:model="authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name"
                                        />
                                    <span dusk='output.authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'>{{ optional($comment->author)->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <button wire:click="save" type="button" dusk="save">Save</button>
</div>
HTML;
    }
}
