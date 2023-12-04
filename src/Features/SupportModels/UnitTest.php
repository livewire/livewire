<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Livewire;
use Sushi\Sushi;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    public function model_properties_are_persisted()
    {
        (new Article)::resolveConnection()->enableQueryLog();

        Livewire::test(new class extends \Livewire\Component {
            public Article $post;

            public function mount() {
                $this->post = Article::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $post->title }}</div>
            HTML; }
        })
        ->assertSee('First')
        ->call('$refresh')
        ->assertSee('First');

        $this->assertCount(2, Article::resolveConnection()->getQueryLog());
    }

    /** @test */
    public function cant_update_a_model_property()
    {
        $this->expectExceptionMessage("Can't set model properties directly");

        Livewire::test(new class extends \Livewire\Component {
            public Article $post;

            public function mount() {
                $this->post = Article::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $post->title }}</div>
            HTML; }
        })
        ->assertSee('First')
        ->set('post.title', 'bar');
    }

    /** @test */
    public function cant_view_model_data_in_javascript()
    {
        $data = Livewire::test(new class extends \Livewire\Component {
            public Article $post;

            public function mount() {
                $this->post = Article::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $post->title }}</div>
            HTML; }
        })->getData();

        $this->assertNull($data['post']);
    }

    /** @test */
    public function unpersisted_models_can_be_assigned_but_no_data_is_persisted_between_requests()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public Article $post;

            public function mount() {
                $this->post = new Article();
            }

            public function render() { return <<<'HTML'
                <div>{{ $post->title }}</div>
            HTML; }
        })
        ->call('$refresh')
        ->assertSet('post', new Article())
        ;
        
        $data = $component->getData();

        $this->assertNull($data['post']);
    }

    /** @test */
    public function model_properties_are_lazy_loaded()
    {
        $this->markTestSkipped(); // @todo: probably not going to go this route...
        (new Article)::resolveConnection()->enableQueryLog();

        Livewire::test(new class extends \Livewire\Component {
            #[Lazy]
            public Article $post;

            public function mount() {
                $this->post = Article::first();
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

        $this->assertCount(2, Article::resolveConnection()->getQueryLog());
    }


    /** @test */
    public function it_uses_laravels_morph_map_instead_of_class_name_if_available_when_dehydrating()
    {
        Relation::morphMap([
            'post' => Article::class,
        ]);

        $component =  Livewire::test(ArticleComponent::class);

        $this->assertEquals('post', $component->snapshot['data']['post'][1]['class']);
    }

    /** @test */
    public function it_uses_laravels_morph_map_instead_of_class_name_if_available_when_hydrating()
    {
        $post = Article::first();

        Relation::morphMap([
            'post' => Article::class,
        ]);

        Livewire::test(ArticleComponent::class)
            ->call('$refresh')
            ->assertSet('post', $post);
    }

}

#[\Attribute]
class Lazy {
    //
}

class ArticleComponent extends \Livewire\Component
{
    public $post;

    public function mount()
    {
        $this->post = Article::first();
    }

    public function render()
    {
        return <<<'HTML'
        <div></div>
        HTML;
    }
}

class Article extends Model
{
    use Sushi;

    protected $rows = [
        ['title' => 'First'],
        ['title' => 'Second'],
    ];
}
