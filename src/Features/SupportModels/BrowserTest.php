<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Features\SupportEvents\BaseOn;
use Livewire\Livewire;
use Sushi\Sushi;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function parent_component_with_eloquent_collection_property_does_not_error_when_child_deletes_a_model_contained_within_it()
    {
        // Ensure sushi has all models each time
        Post::firstOrCreate(['id' => 1, 'title' => 'Post #1']);
        Post::firstOrCreate(['id' => 2, 'title' => 'Post #2']);
        Post::firstOrCreate(['id' => 3, 'title' => 'Post #3']);

        Livewire::visit([
            new class extends Component {
                public $posts;

                public function mount()
                {
                    $this->posts = Post::all();
                }

                #[BaseOn('postDeleted')]
                public function setPosts() {
                    $this->posts = Post::all();
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
                    Post::find($id)->delete();
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
}

class Post extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'title' => 'Post #1'],
        ['id' => 2, 'title' => 'Post #2'],
        ['id' => 3, 'title' => 'Post #3'],
    ];
}
