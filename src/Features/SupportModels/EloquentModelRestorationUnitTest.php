<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Tests\TestComponent;

class EloquentModelRestorationUnitTest extends \Tests\TestCase
{
    public function test_model_synth_reuses_models_remembered_by_collection_synth()
    {
        // Boot Sushi so resolveConnection() is real.
        Post::first();
        app('livewire')->flushState();

        $connection = Post::resolveConnection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();

        $component = new class extends TestComponent {};
        $context = new ComponentContext($component);

        $collectionSynth = new EloquentCollectionSynth($context, 'posts');
        $modelSynth = new ModelSynth($context, 'first');

        // Hydrate a collection of both posts (may be a lazy proxy on PHP 8.4+).
        $collection = $collectionSynth->hydrate(null, [
            'keys' => [1, 2],
            'class' => Collection::class,
            'modelClass' => Post::class,
        ], fn ($property, $value) => $value);

        // Materialize the collection — one bulk SELECT; models are remembered.
        $this->assertCount(2, $collection);

        $queriesAfterCollection = $connection->getQueryLog();
        $this->assertCount(1, $queriesAfterCollection);

        // Hydrate a single model that was part of that collection.
        $model = $modelSynth->hydrate(null, ['class' => Post::class, 'key' => 1]);

        // Materialize the model — must not issue another SELECT.
        $this->assertSame('First', $model->title);

        $this->assertCount(count($queriesAfterCollection), $connection->getQueryLog());
    }

    public function test_same_model_is_only_queried_once_when_hydrated_on_multiple_properties()
    {
        Post::first();
        app('livewire')->flushState();

        $connection = Post::resolveConnection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();

        $component = new class extends TestComponent {};
        $context = new ComponentContext($component);
        $synth = new ModelSynth($context, 'post');

        $a = $synth->hydrate(null, ['class' => Post::class, 'key' => 1]);
        $b = $synth->hydrate(null, ['class' => Post::class, 'key' => 1]);

        // Force materialization (no-ops on PHP < 8.4 where hydrate already queried).
        $this->assertSame('First', $a->title);
        $this->assertSame('First', $b->title);
        $this->assertSame($a->getKey(), $b->getKey());

        $postQueries = array_values(array_filter(
            $connection->getQueryLog(),
            fn ($q) => str_contains($q['query'], 'posts')
        ));

        // One restoration for the same class:key, not two.
        $this->assertCount(1, $postQueries);
    }

    public function test_collection_and_single_model_of_same_class_share_one_query_on_refresh()
    {
        Post::first();
        app('livewire')->flushState();

        $connection = Post::resolveConnection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();

        Livewire::test(new class extends TestComponent {
            public Collection $posts;
            public Post $selected;

            public function mount()
            {
                $this->posts = Post::all();
                $this->selected = $this->posts->first();
            }

            public function render()
            {
                return <<<'HTML'
                    <div>
                        @foreach ($posts as $article)
                            {{ $article->title }}
                        @endforeach
                        selected: {{ $selected->title }}
                    </div>
                HTML;
            }
        })
            ->assertSee('First')
            ->assertSee('Second')
            ->assertSee('selected: First')
            ->call('$refresh')
            ->assertSee('First')
            ->assertSee('Second')
            ->assertSee('selected: First');

        $postQueries = array_values(array_filter(
            $connection->getQueryLog(),
            fn ($q) => str_contains($q['query'], 'posts')
        ));

        // Mount: 1× SELECT for the collection (selected is the same instance, no extra query).
        // Refresh: 1× bulk restore for the collection; selected reuses the cached model.
        // Total: 2 queries, not 3 (or more).
        $this->assertCount(2, $postQueries);
    }

    public function test_cached_model_does_not_leak_relations()
    {
        Post::first();
        app('livewire')->flushState();

        $mechanism = app(PersistentMiddleware::class);

        $post = Post::first();
        $post->setRelation('author', Author::first());

        $this->assertTrue($post->relationLoaded('author'));

        $mechanism->rememberResolvedModel($post);

        $resolved = $mechanism->getResolvedRouteModel(Post::class, $post->getKey());

        $this->assertNotNull($resolved);
        $this->assertFalse($resolved->relationLoaded('author'));
        $this->assertSame($post->getKey(), $resolved->getKey());
        $this->assertSame('First', $resolved->title);
    }
}

class Post extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['title' => 'First'],
        ['title' => 'Second'],
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}

class Author extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'Jane'],
    ];
}
