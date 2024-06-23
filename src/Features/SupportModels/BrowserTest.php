<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Component;
use Livewire\Features\SupportEvents\BaseOn;
use Livewire\Livewire;
use Sushi\Sushi;

class BrowserTest extends \Tests\BrowserTestCase
{
    use RefreshDatabase;

    public function test_parent_component_with_eloquent_collection_property_does_not_error_when_child_deletes_a_model_contained_within_it()
    {
        Livewire::visit([
            new class extends Component {
                public $posts;

                public function mount()
                {
                    $this->posts = BrowserTestPost::all();
                }

                #[BaseOn('postDeleted')]
                public function setPosts() {
                    $this->posts = BrowserTestPost::all();
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        @foreach($posts as $post)
                            <div wire:key="parent-post-{{ $post->id }}">
                                <livewire:child wire:key="{{ $post->id }}" :post="$post" />
                            </div>
                        @endforeach
                    </div>
                    HTML;
                }
            },
            'child' => new class extends Component {
                public $post;

                public function delete($id)
                {
                    BrowserTestPost::find($id)->delete();
                    $this->dispatch('postDeleted');
                }

                public function render()
                {
                    return <<<'HTML'
                    <div dusk="post-{{ $post->id }}">
                        {{ $post->title }}

                        <button dusk="delete-{{ $post->id }}" wire:click="delete({{ $post->id }})">Delete</button>
                    </div>
                    HTML;
                }
            },
        ])
            ->waitForLivewireToLoad()
            ->assertPresent('@post-1')
            ->assertSeeIn('@post-1', 'Post #1')
            ->waitForLivewire()->click('@delete-1')
            ->assertNotPresent('@parent-post-1')
            ;
    }

    public function test_empty_eloquent_collection_property_is_dehydrated_without_errors()
    {
        Livewire::visit([
            new class extends Component
            {
                public $placeholder = 'Original text';

                public $posts;

                public EloquentCollection $typedPostsNotInitialized;

                public EloquentCollection $typedPostsInitialized;

                public function mount()
                {
                    $this->posts = new EloquentCollection();
                    $this->typedPostsInitialized = new EloquentCollection();
                }

                function changePlaceholder()
                {
                    $this->placeholder = 'New text';
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button dusk="changePlaceholder" wire:click="changePlaceholder">Change placeholder</button>
                        <span dusk="placeholder">{{ $placeholder }}</span>
                        <span dusk="postsIsEloquentCollection">{{ $posts instanceof \Illuminate\Database\Eloquent\Collection ? 'true' : 'false'  }}</span>
                        <span dusk="typedPostsNotInitializedIsEloquentCollection">{{ $typedPostsNotInitialized instanceof \Illuminate\Database\Eloquent\Collection ? 'true' : 'false' }}</span>
                        <span dusk="typedPostsInitializedIsEloquentCollection">{{ $typedPostsInitialized instanceof \Illuminate\Database\Eloquent\Collection ? 'true' : 'false' }}</span>
                    </div>
                    HTML;
                }
            },

        ])
            ->waitForLivewireToLoad()
            ->assertSeeIn('@placeholder', 'Original text')
            ->assertSeeIn('@postsIsEloquentCollection', 'true')
            ->assertSeeIn('@typedPostsNotInitializedIsEloquentCollection', 'false')
            ->assertSeeIn('@typedPostsInitializedIsEloquentCollection', 'true')
            ->waitForLivewire()->click('@changePlaceholder')
            ->assertSeeIn('@placeholder', 'New text')
            ->assertSeeIn('@postsIsEloquentCollection', 'true')
            ->assertSeeIn('@typedPostsNotInitializedIsEloquentCollection', 'false')
            ->assertSeeIn('@typedPostsInitializedIsEloquentCollection', 'true')
            ;
    }
}

class BrowserTestPost extends Model
{
    use Sushi;

    protected $guarded = [];

    public function getRows() {
        return [
            ['id' => 1, 'title' => 'Post #1'],
            ['id' => 2, 'title' => 'Post #2'],
            ['id' => 3, 'title' => 'Post #3'],
        ];
    }
}
