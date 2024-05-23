<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\TestComponent;

class UnitTest extends \Tests\TestCase
{
    public function test_model_properties_are_persisted()
    {
        (new Article)::resolveConnection()->enableQueryLog();

        Livewire::test(new class extends \Livewire\Component {
            public Article $article;

            public function mount() {
                $this->article = Article::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $article->title }}</div>
            HTML; }
        })
        ->assertSee('First')
        ->call('$refresh')
        ->assertSee('First');

        $this->assertCount(2, Article::resolveConnection()->getQueryLog());
    }

    public function test_cant_update_a_model_property()
    {
        $this->expectExceptionMessage("Can't set model properties directly");

        Livewire::test(new class extends \Livewire\Component {
            public Article $article;

            public function mount() {
                $this->article = Article::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $article->title }}</div>
            HTML; }
        })
        ->assertSee('First')
        ->set('article.title', 'bar');
    }

    public function test_cant_view_model_data_in_javascript()
    {
        $data = Livewire::test(new class extends \Livewire\Component {
            public Article $article;

            public function mount() {
                $this->article = Article::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $article->title }}</div>
            HTML; }
        })->getData();

        $this->assertNull($data['article']);
    }

    public function test_unpersisted_models_can_be_assigned_but_no_data_is_persisted_between_requests()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public Article $article;

            public function mount() {
                $this->article = new Article();
            }

            public function render() { return <<<'HTML'
                <div>{{ $article->title }}</div>
            HTML; }
        })
        ->call('$refresh')
        ->assertSet('article', new Article())
        ;

        $data = $component->getData();

        $this->assertNull($data['article']);
    }

    public function test_model_properties_are_lazy_loaded()
    {
        $this->markTestSkipped(); // @todo: probably not going to go this route...
        (new Article)::resolveConnection()->enableQueryLog();

        Livewire::test(new class extends TestComponent {
            #[Lazy]
            public Article $article;

            public function mount() {
                $this->article = Article::first();
            }

            public function save()
            {
                $this->article->save();
            }
        })
        ->call('$refresh')
        ->call('save');

        $this->assertCount(2, Article::resolveConnection()->getQueryLog());
    }


    public function test_it_uses_laravels_morph_map_instead_of_class_name_if_available_when_dehydrating()
    {
        Relation::morphMap([
            'article' => Article::class,
        ]);

        $component =  Livewire::test(ArticleComponent::class);

        $this->assertEquals('article', $component->snapshot['data']['article'][1]['class']);
    }

    public function test_it_uses_laravels_morph_map_instead_of_class_name_if_available_when_hydrating()
    {
        $article = Article::first();

        Relation::morphMap([
            'article' => Article::class,
        ]);

        Livewire::test(ArticleComponent::class)
            ->call('$refresh')
            ->assertSet('article', $article);
    }

    public function test_collections_with_duplicate_models_are_available_when_hydrating()
    {
        Livewire::test(new class extends \Livewire\Component {
            public Collection $articles;

            public function mount() {
                $this->articles = new Collection([
                    Article::first(),
                    Article::first(),
                ]);
            }

            public function render() { return <<<'HTML'
                <div>
                    @foreach($articles as $article)
                    {{ $article->title.'-'.$loop->index }}
                    @endforeach
                </div>
            HTML; }
        })
        ->assertSee('First-0')
        ->assertSee('First-1')
        ->call('$refresh')
        ->assertSee('First-0')
        ->assertSee('First-1');
    }

    public function test_collections_retain_their_order_on_hydration()
    {
        Livewire::test(new class extends \Livewire\Component {
            public Collection $articles;

            public function mount() {
                $this->articles = Article::all()->reverse();
            }

            public function render() { return <<<'HTML'
                <div>
                    @foreach($articles as $article)
                    {{ $article->title.'-'.$loop->index }}
                    @endforeach
                </div>
            HTML; }
        })
        ->assertSee('Second-0')
        ->assertSee('First-1')
        ->call('$refresh')
        ->assertSee('Second-0')
        ->assertSee('First-1');
    }

    public function test_it_does_not_trigger_ClassMorphViolationException_when_morh_map_is_enforced()
    {
        // reset morph
        Relation::morphMap([], false);
        Relation::requireMorphMap();

        $component = Livewire::test(new class extends TestComponent {
            public $article;

            public function mount()
            {
                $this->article = Article::first();
            }
        });

        $this->assertEquals(Article::class, $component->snapshot['data']['article'][1]['class']);

        Relation::requireMorphMap(false);
    }
}

#[\Attribute]
class Lazy {
    //
}

class ArticleComponent extends TestComponent
{
    public $article;

    public function mount()
    {
        $this->article = Article::first();
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
