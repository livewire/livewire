<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('posts', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('description');
            $table->string('content');
            $table->timestamps();
        });
    }

    /** @test */
    public function model_properties_are_persisted()
    {
        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content']);

        (new Post)::resolveConnection()->enableQueryLog();

        Livewire::test(new class extends \Livewire\Component {
            public Post $post;

            public function mount() {
                $this->post = Post::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $post->title }}</div>
            HTML; }
        })
        ->assertSee('Post 1')
        ->call('$refresh')
        ->assertSee('Post 1');

        $this->assertCount(2, Post::resolveConnection()->getQueryLog());
    }

    /** @test */
    public function cant_update_a_model_property()
    {
        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content']);

        $this->expectExceptionMessage("Can't set model properties directly");

        Livewire::test(new class extends \Livewire\Component {
            public Post $post;

            public function mount() {
                $this->post = Post::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $post->title }}</div>
            HTML; }
        })
        ->assertSee('Post 1')
        ->set('post.title', 'bar');
    }

    /** @test */
    public function cant_view_model_data_in_javascript()
    {
        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content']);

        $data = Livewire::test(new class extends \Livewire\Component {
            public Post $post;

            public function mount() {
                $this->post = Post::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $post->title }}</div>
            HTML; }
        })->getData();

        $this->assertNull($data['post']);
    }

    /** @test */
    public function model_properties_are_lazy_loaded()
    {
        $this->markTestSkipped(); // @todo: probably not going to go this route...
        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content']);

        (new Post)::resolveConnection()->enableQueryLog();

        Livewire::test(new class extends \Livewire\Component {
            #[Lazy]
            public Post $post;

            public function mount() {
                $this->post = Post::first();
            }

            public function save()
            {
                $this->post->save();
            }

            public function render() { return <<<'HTML'
                <div></div>
            HTML; }
        })
        ->call('$refresh')
        ->call('save');

        $this->assertCount(2, Post::resolveConnection()->getQueryLog());
    }


    /** @test */
    public function it_uses_laravels_morph_map_instead_of_class_name_if_available_when_dehydrating()
    {
        $post = Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);

        Relation::morphMap([
            'post' => Post::class,
        ]);

        $component =  Livewire::test(PostComponent::class);

        $this->assertEquals('post', $component->snapshot['data']['post'][1]['class']);
    }

    /** @test */
    public function it_uses_laravels_morph_map_instead_of_class_name_if_available_when_hydrating()
    {
        $post = Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);

        $post = $post->fresh();

        Relation::morphMap([
            'post' => Post::class,
        ]);

        Livewire::test(PostComponent::class)
            ->call('$refresh')
            ->assertSet('post', $post);
    }

}

#[\Attribute]
class Lazy {
    //
}

class PostComponent extends \Livewire\Component
{
    public $post;

    public function mount()
    {
        $this->post = Post::first();
    }

    public function render()
    {
        return <<<'HTML'
        <div></div>
        HTML;
    }
}
class Post extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];
}
