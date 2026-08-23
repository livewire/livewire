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
        $this->assertCount(1, $connection->getQueryLog());

        // Hydrate two models that was part of that collection.
        $a = $modelSynth->hydrate(null, ['class' => Post::class, 'key' => 1]);
        $b = $modelSynth->hydrate(null, ['class' => Post::class, 'key' => 2]);
        $this->assertSame('First', $a->title);
        $this->assertSame('Second', $b->title);

        // Same instances that were remembered — not newly queried models.
        $this->assertTrue($a->is($collection->firstWhere('id', 1)));
        $this->assertTrue($b->is($collection->firstWhere('id', 2)));

        $this->assertCount(1, $connection->getQueryLog());
    }

    public function test_collection_reuses_already_materialized_single_models()
    {
        Post::first();
        app('livewire')->flushState();

        $connection = Post::resolveConnection();
        $connection->enableQueryLog();
        $connection->flushQueryLog();

        $component = new class extends TestComponent {};
        $context = new ComponentContext($component);

        $modelSynth = new ModelSynth($context, 'first');
        $collectionSynth = new EloquentCollectionSynth($context, 'posts');

        $a = $modelSynth->hydrate(null, ['class' => Post::class, 'key' => 1]);
        $b = $modelSynth->hydrate(null, ['class' => Post::class, 'key' => 2]);
        $this->assertSame('First', $a->title);
        $this->assertSame('Second', $b->title);

        $this->assertCount(2, $connection->getQueryLog());

        $collection = $collectionSynth->hydrate(null, [
            'keys' => [1, 2],
            'class' => Collection::class,
            'modelClass' => Post::class,
        ], fn ($property, $value) => $value);

        $this->assertCount(2, $collection);

        // Same instances that were remembered — not newly queried models.
        $this->assertTrue($a->is($collection->firstWhere('id', 1)));
        $this->assertTrue($b->is($collection->firstWhere('id', 2)));

        // Still 2 — collection did not query again.
        $this->assertCount(2, $connection->getQueryLog());
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
        $this->assertSame('First', $a->title);
        $this->assertSame('First', $b->title);
        $this->assertTrue($a->is($b));

        // One restoration for the same class:key, not two.
        $this->assertCount(1, $connection->getQueryLog());
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

        // Mount: 1× SELECT for the collection (selected is the same instance, no extra query).
        // Refresh: 1× bulk restore for the collection; selected reuses the cached model.
        // Total: 2 queries, not 3 (or more).
        $this->assertCount(2, $connection->getQueryLog());
    }

    public function test_cached_model_preserves_default_eager_loads_from_restoration()
    {
        // PostWithAuthor declares protected $with = ['author'].
        // newQueryForRestoration applies that $with, so a correct cache entry
        // must still have author loaded — withoutRelations() would break this.
        PostWithAuthor::first();
        Author::first();
        app('livewire')->flushState();

        $component = new class extends TestComponent {};
        $context = new ComponentContext($component);

        $collectionSynth = new EloquentCollectionSynth($context, 'posts');
        $modelSynth = new ModelSynth($context, 'first');

        $collection = $collectionSynth->hydrate(null, [
            'keys' => [1],
            'class' => Collection::class,
            'modelClass' => PostWithAuthor::class,
        ], fn ($property, $value) => $value);

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->first()->relationLoaded('author'));

        // Reuse via ModelSynth — must keep the same restoration shape ($with intact).
        $model = $modelSynth->hydrate(null, [
            'class' => PostWithAuthor::class,
            'key' => 1,
        ]);

        $this->assertSame('First', $model->title);
        $this->assertTrue($model->relationLoaded('author'));
        $this->assertSame('Jane', $model->author->name);
    }

    public function test_first_materialization_wins_and_is_not_overwritten()
    {
        Post::first();
        app('livewire')->flushState();

        $mechanism = app(PersistentMiddleware::class);

        $model = Post::first();
        $mechanism->rememberResolvedModel($model);

        $second = Post::first();
        $second->setRelation('author', Author::first());
        $this->assertTrue($second->relationLoaded('author'));

        // First write wins — a later remember must not replace the cached instance.
        $mechanism->rememberResolvedModel($second);

        $resolvedModel = $mechanism->getResolvedRouteModel(Post::class, $model->getKey());

        $this->assertNotNull($resolvedModel);
        $this->assertFalse($resolvedModel->relationLoaded('author'));
        $this->assertTrue($resolvedModel->is($model));
    }
}

class Post extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['title' => 'First', 'author_id' => 1],
        ['title' => 'Second', 'author_id' => 2],
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}

class PostWithAuthor extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $table = 'posts';

    protected $rows = [
        ['title' => 'First', 'author_id' => 1],
        ['title' => 'Second', 'author_id' => 2],
    ];

    protected $with = ['author'];

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
        ['id' => 2, 'name' => 'Brian'],
    ];
}
