<?php

namespace Tests\Browser\DataBinding\Models;

use Illuminate\Support\Facades\View;
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

    public function render()
    {
        return
        <<<'HTML'
<div>
    <div>
        Author Name<input dusk='author.name' wire:model='author.name' />
        Author Email<input dusk='author.email' wire:model='author.email' />
    </div>

    <div>
        @foreach($author->posts as $postKey => $post)
            <div>
                Post Title<input dusk='author.posts.{{ $postKey }}.title' wire:model="author.posts.{{ $postKey }}.title" />

                <div>
                    @foreach($post->comments as $commentKey => $comment)
                        <div>
                            Comment Comment<input
                                dusk='author.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'
                                wire:model="author.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment"
                                />
                            Commment Author Name<input
                                dusk='author.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'
                                wire:model="author.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name"
                                />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
HTML;
    }
}
