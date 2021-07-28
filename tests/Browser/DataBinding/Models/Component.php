<?php

namespace Tests\Browser\DataBinding\Models;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $author;

    protected $rules = [
        'author.name' => '',
        'author.email' => '',
        'author.posts.*.title' => '',
        'author.posts.*.comments.*.comment' => '',
        'author.posts.*.comments.*.author.name' => '',
    ];

    public function mount()
    {
        $this->author = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->first();
    }

    public function save()
    {
        $this->author->push();
    }

    public function render()
    {
        return
        <<<'HTML'
<div>
    <div>
        Author Name
        <input dusk='author.name' wire:model='author.name' />
        <span dusk='output.author.name'>{{ $author->name }}</span>

        Author Email
        <input dusk='author.email' wire:model='author.email' />
        <span dusk='output.author.email'>{{ $author->email }}</span>
    </div>

    <div>
        @foreach($author->posts as $postKey => $post)
            <div>
                Post Title
                <input dusk='author.posts.{{ $postKey }}.title' wire:model="author.posts.{{ $postKey }}.title" />
                <span dusk='output.author.posts.{{ $postKey }}.title'>{{ $post->title }}</span>

                <div>
                    @foreach($post->comments as $commentKey => $comment)
                        <div>
                            Comment Comment
                            <input
                                dusk='author.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'
                                wire:model="author.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment"
                                />
                            <span dusk='output.author.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'>{{ $comment->comment }}</span>

                            Commment Author Name
                            <input
                                dusk='author.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'
                                wire:model="author.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name"
                                />
                            <span dusk='output.author.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'>{{ optional($comment->author)->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <button wire:click="save" type="button" dusk="save">Save</button>
</div>
HTML;
    }
}
