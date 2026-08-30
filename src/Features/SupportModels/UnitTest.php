<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Livewire\Drawer\Utils;
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

    public function test_it_does_not_trigger_ClassMorphViolationException_for_collections_when_morph_map_is_enforced()
    {
        // reset morph
        Relation::morphMap([], false);
        Relation::requireMorphMap();

        $component = Livewire::test(new class extends TestComponent {
            public Collection $articles;

            public function mount()
            {
                $this->articles = Article::all();
            }
        });

        $this->assertEquals(Article::class, $component->snapshot['data']['articles'][1]['modelClass']);

        Relation::requireMorphMap(false);
    }

    public function test_it_dehydrates_models_with_overridden_getMorphClass_using_actual_class()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public $article;

            public function mount() {
                $this->article = ExtendedArticle::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $article->title }}</div>
            HTML; }
        });

        $this->assertEquals(ExtendedArticle::class, $component->snapshot['data']['article'][1]['class']);
    }

    public function test_it_hydrates_models_with_overridden_getMorphClass_using_actual_class()
    {
        Livewire::test(new class extends \Livewire\Component {
            public $article;

            public function mount() {
                $this->article = ExtendedArticle::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $article->title }}</div>
            HTML; }
        })
        ->call('$refresh')
        ->assertSet('article', ExtendedArticle::first());
    }

    public function test_model_synth_rejects_non_model_classes()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid model class');

        $component = Livewire::test(ArticleComponent::class);

        // Create a synth instance and try to hydrate with a non-Model class
        $synth = new ModelSynth(
            new \Livewire\Mechanisms\HandleComponents\ComponentContext($component->instance()),
            'article'
        );

        // This should throw because stdClass doesn't extend Model
        $synth->hydrate(null, ['class' => \stdClass::class]);
    }

    public function test_route_bound_models_are_not_queried_twice_on_update()
    {
        Route::livewire('/test-articles/{article}', RouteModelBindingComponent::class)
            ->middleware('web');

        // GET the page to mount the component and get a valid snapshot
        $response = $this->withoutExceptionHandling()->get('/test-articles/1');
        $response->assertOk();
        $response->assertSee('First');

        // Extract snapshot from the rendered HTML
        $html = $response->getContent();
        $snapshot = Utils::extractAttributeDataFromHtml($html, 'wire:snapshot');
        $encodedSnapshot = json_encode($snapshot);

        // Flush state from the initial render
        app('livewire')->flushState();

        // Enable query log and clear it
        Article::resolveConnection()->enableQueryLog();
        Article::resolveConnection()->flushQueryLog();

        // POST to the update endpoint to trigger an update request
        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(app('livewire')->getUpdateUri(), [
                'components' => [
                    [
                        'snapshot' => $encodedSnapshot,
                        'calls' => [['method' => '$refresh', 'params' => [], 'path' => '']],
                        'updates' => [],
                    ],
                ],
            ])->assertOk();

        // Count queries to the articles table — should only be one, not two
        $queryLog = Article::resolveConnection()->getQueryLog();
        $articleQueries = array_filter($queryLog, fn ($q) => str_contains($q['query'], 'articles'));

        $this->assertCount(1, array_values($articleQueries));
    }

    public function test_a_model_property_reuses_a_model_already_hydrated_by_a_collection_property()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public Collection $articles;

            public Article $selected;

            public function mount() {
                $this->articles = Article::all();
                $this->selected = $this->articles->first();
            }

            public function render() { return <<<'HTML'
                <div>
                    @foreach($articles as $article)
                    {{ $article->title.'-'.$loop->index }}
                    @endforeach
                    {{ 'Selected: '.$selected->title }}
                </div>
            HTML; }
        });

        Article::resolveConnection()->enableQueryLog();
        Article::resolveConnection()->flushQueryLog();

        $component->call('$refresh')
            ->assertSee('First-0')
            ->assertSee('Second-1')
            ->assertSee('Selected: First');

        $this->assertCount(1, Article::resolveConnection()->getQueryLog());
    }

    public function test_a_collection_property_only_queries_models_not_already_hydrated_by_a_model_property()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public Article $selected;

            public Collection $articles;

            public function mount() {
                $this->selected = Article::first();
                $this->articles = Article::all();
            }

            public function render() { return <<<'HTML'
                <div>
                    {{ 'Selected: '.$selected->title }}
                    @foreach($articles as $article)
                    {{ $article->title.'-'.$loop->index }}
                    @endforeach
                </div>
            HTML; }
        });

        Article::resolveConnection()->enableQueryLog();
        Article::resolveConnection()->flushQueryLog();

        $component->call('$refresh')
            ->assertSee('Selected: First')
            ->assertSee('First-0')
            ->assertSee('Second-1');

        $queryLog = Article::resolveConnection()->getQueryLog();

        $this->assertCount(2, $queryLog);

        // The collection query should only restore the model that wasn't already
        // resolved by the model property. Integer keys may be inlined into the
        // SQL or passed as bindings depending on the Laravel version...
        [$modelQuery, $collectionQuery] = $queryLog;

        $this->assertTrue(
            str_contains($collectionQuery['query'], 'in (2)') || $collectionQuery['bindings'] === [2],
            'The collection should only have queried for the unresolved model',
        );
    }

    public function test_two_model_properties_with_the_same_key_are_hydrated_with_one_query()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public Article $first;

            public Article $second;

            public function mount() {
                $this->first = Article::first();
                $this->second = Article::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ 'a:'.$first->title }} {{ 'b:'.$second->title }}</div>
            HTML; }
        });

        Article::resolveConnection()->enableQueryLog();
        Article::resolveConnection()->flushQueryLog();

        $component->call('$refresh')
            ->assertSee('a:First')
            ->assertSee('b:First');

        $this->assertCount(1, Article::resolveConnection()->getQueryLog());
    }

    public function test_reused_model_instances_are_shared_within_a_request()
    {
        Livewire::test(new class extends \Livewire\Component {
            public Collection $articles;

            public Article $selected;

            public function mount() {
                $this->articles = Article::all();
                $this->selected = $this->articles->first();
            }

            public function rename() {
                $this->articles->count();

                $this->selected->title = 'Renamed';
            }

            public function render() { return <<<'HTML'
                <div>
                    @foreach($articles as $article)
                    {{ $article->title.'-'.$loop->index }}
                    @endforeach
                </div>
            HTML; }
        })
        ->call('rename')
        ->assertSee('Renamed-0')
        ->assertSee('Second-1');
    }

    public function test_a_collection_is_not_queried_when_all_its_models_are_already_resolved()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public Article $one;

            public Article $two;

            public Collection $articles;

            public function mount() {
                $this->one = Article::find(1);
                $this->two = Article::find(2);
                $this->articles = new Collection([Article::find(1), Article::find(1), Article::find(2)]);
            }

            public function render() { return <<<'HTML'
                <div>
                    {{ 'a:'.$one->title }} {{ 'b:'.$two->title }}
                    @foreach($articles as $article)
                    {{ $article->title.'-'.$loop->index }}
                    @endforeach
                </div>
            HTML; }
        });

        Article::resolveConnection()->enableQueryLog();
        Article::resolveConnection()->flushQueryLog();

        $component->call('$refresh')
            ->assertSee('a:First')
            ->assertSee('b:Second')
            ->assertSee('First-0')
            ->assertSee('First-1')
            ->assertSee('Second-2');

        $this->assertCount(2, Article::resolveConnection()->getQueryLog());
    }

    public function test_resolved_models_are_not_reused_across_requests()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public Article $article;

            public function mount() {
                $this->article = Article::first();
            }

            public function render() { return <<<'HTML'
                <div>{{ $article->title }}</div>
            HTML; }
        });

        Article::resolveConnection()->enableQueryLog();
        Article::resolveConnection()->flushQueryLog();

        $component->call('$refresh')->assertSee('First');
        $component->call('$refresh')->assertSee('First');

        $this->assertCount(2, Article::resolveConnection()->getQueryLog());
    }

    public function test_a_mutated_model_is_not_reused_by_other_properties()
    {
        // On earlier versions there are no lazy proxies, so properties hydrate
        // eagerly before any mutations and share the still-clean instance...
        if (PHP_VERSION_ID < 80400) $this->markTestSkipped('Requires lazy model proxies');

        $component = Livewire::test(new class extends \Livewire\Component {
            public Article $first;

            public Article $second;

            public function mount() {
                $this->first = Article::first();
                $this->second = Article::first();
            }

            public function rename() {
                $this->first->title = 'Draft';
            }

            public function render() { return <<<'HTML'
                <div>{{ 'a:'.$first->title }} {{ 'b:'.$second->title }}</div>
            HTML; }
        });

        Article::resolveConnection()->enableQueryLog();
        Article::resolveConnection()->flushQueryLog();

        // The unsaved mutation on $first shouldn't leak into $second — it
        // should fall through to a fresh query instead...
        $component->call('rename')
            ->assertSee('a:Draft')
            ->assertSee('b:First');

        $this->assertCount(2, Article::resolveConnection()->getQueryLog());
    }

    public function test_a_deleted_model_is_not_reused_when_hydrating_a_collection()
    {
        // On earlier versions there are no lazy proxies, so the collection is
        // hydrated eagerly before the deletion, like it is on main...
        if (PHP_VERSION_ID < 80400) $this->markTestSkipped('Requires lazy model proxies');

        $this->resetMutableArticles();

        Livewire::test(new class extends \Livewire\Component {
            public Collection $articles;

            public MutableArticle $selected;

            public function mount() {
                $this->articles = MutableArticle::all();
                $this->selected = $this->articles->first();
            }

            public function deleteSelected() {
                $this->selected->delete();
            }

            public function render() { return <<<'HTML'
                <div>
                    @foreach($articles as $article)
                    {{ $article->title.'-'.$loop->index }}
                    @endforeach
                </div>
            HTML; }
        })
        ->call('deleteSelected')
        ->assertDontSee('First-')
        ->assertSee('Second-0');
    }

    public function test_a_saved_model_is_not_reused_by_other_properties()
    {
        if (PHP_VERSION_ID < 80400) $this->markTestSkipped('Requires lazy model proxies');

        $this->resetMutableArticles();

        $component = Livewire::test(new class extends \Livewire\Component {
            public MutableArticle $first;

            public MutableArticle $second;

            public function mount() {
                $this->first = MutableArticle::find(1);
                $this->second = MutableArticle::find(1);
            }

            public function rename() {
                $this->first->update(['title' => 'Renamed']);
            }

            public function render() { return <<<'HTML'
                <div>{{ 'a:'.$first->title }} {{ 'b:'.$second->title }}</div>
            HTML; }
        });

        MutableArticle::resolveConnection()->enableQueryLog();
        MutableArticle::resolveConnection()->flushQueryLog();

        // The saved model shouldn't be reused for $second — it should fall
        // through to a fresh query, which reads the same saved title...
        $component->call('rename')
            ->assertSee('a:Renamed')
            ->assertSee('b:Renamed');

        $selects = array_filter(
            MutableArticle::resolveConnection()->getQueryLog(),
            fn ($q) => str_starts_with($q['query'], 'select'),
        );

        $this->assertCount(2, $selects);
    }

    public function test_a_model_saved_after_its_reuse_was_rejected_is_not_served_stale()
    {
        if (PHP_VERSION_ID < 80400) $this->markTestSkipped('Requires lazy model proxies');

        $this->resetMutableArticles();

        Livewire::test(new class extends \Livewire\Component {
            public MutableArticle $first;

            public MutableArticle $second;

            public Collection $articles;

            public function mount() {
                $this->first = MutableArticle::find(1);
                $this->second = MutableArticle::find(1);
                $this->articles = MutableArticle::all();
            }

            public function rename() {
                // Once a mutated instance is encountered, the model shouldn't be
                // shared for the rest of the request — even after the collection
                // resolves a clean instance while the mutation is unsaved...
                $this->first->title = 'Renamed';

                $this->articles->count();

                $this->first->save();
            }

            public function render() { return <<<'HTML'
                <div>{{ 'b:'.$second->title }}</div>
            HTML; }
        })
        ->call('rename')
        ->assertSee('b:Renamed');
    }

    public function test_a_model_deleted_after_its_reuse_was_rejected_is_not_resurrected()
    {
        if (PHP_VERSION_ID < 80400) $this->markTestSkipped('Requires lazy model proxies');

        $this->resetMutableArticles();

        Livewire::test(new class extends \Livewire\Component {
            public MutableArticle $selected;

            public Collection $articles;

            public Collection $others;

            public function mount() {
                $this->selected = MutableArticle::find(1);
                $this->articles = MutableArticle::all();
                $this->others = MutableArticle::all();
            }

            public function deleteSelected() {
                $this->selected->title = 'Doomed';

                $this->articles->count();

                $this->selected->delete();
            }

            public function render() { return <<<'HTML'
                <div>
                    @foreach($others as $article)
                    {{ $article->title.'-'.$loop->index }}
                    @endforeach
                </div>
            HTML; }
        })
        ->call('deleteSelected')
        ->assertDontSee('First-')
        ->assertDontSee('Doomed-')
        ->assertSee('Second-0');
    }

    protected function resetMutableArticles()
    {
        // Reset the table as these tests modify or delete its rows...
        MutableArticle::query()->delete();
        MutableArticle::insert([
            ['id' => 1, 'title' => 'First'],
            ['id' => 2, 'title' => 'Second'],
        ]);
    }

    public function test_hydrating_an_empty_eloquent_collection_does_not_trigger_deprecations()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public Collection $articles;

            public function mount() {
                $this->articles = new Collection();
            }

            public function render() { return <<<'HTML'
                <div>count: {{ count($articles) }}</div>
            HTML; }
        });

        // An empty collection dehydrates with `modelClass` as `null`. Convert
        // deprecations to exceptions so the test fails if hydrating passes
        // that `null` as an array offset (deprecated in PHP 8.5)...
        set_error_handler(function ($severity, $message) {
            throw new \ErrorException($message, 0, $severity);
        }, E_DEPRECATED | E_USER_DEPRECATED);

        try {
            $component->call('$refresh')->assertSee('count: 0');
        } finally {
            restore_error_handler();
        }
    }
}

#[\Attribute]
class Lazy {
    //
}

class ArticleComponent extends \Livewire\Component
{
    public $article;

    public function mount()
    {
        $this->article = Article::first();
    }

    public function render()
    {
        return '<div>{{ $article->title }}</div>';
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

// Uses its own table so modifying or deleting rows doesn't affect other tests...
class MutableArticle extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['title' => 'First'],
        ['title' => 'Second'],
    ];
}

// Simulates tightenco/parental's HasParent trait, which overrides
// getTable() and getMorphClass() to return the parent's values so
// that polymorphic relations use the parent's table.
class ExtendedArticle extends Article
{
    protected $table = 'articles';

    public function getMorphClass()
    {
        return (new Article)->getMorphClass();
    }
}

class RouteModelBindingComponent extends \Livewire\Component
{
    public Article $article;

    public function render()
    {
        return '<div>{{ $article->title }}</div>';
    }
}
