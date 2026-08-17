<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

class UnitTest extends \Tests\TestCase
{
    public function test_reactive_prop_value_is_available_during_boot_hydrate_and_booted_hooks()
    {
        Livewire::component('child-with-lifecycle-hooks', ChildWithLifecycleHooks::class);

        $child = Livewire::test(ChildWithLifecycleHooks::class, ['count' => 0]);
        $this->assertEquals(0, $child->get('count'));

        // Simulate parent passing count=5 on next request
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 5];
        $child->call('$refresh');

        $this->assertEquals(5, $child->get('count'));
        $this->assertEquals(5, $child->get('bootValue'), 'boot() should see the new reactive prop value');
        $this->assertEquals(5, $child->get('hydrateValue'), 'hydrate() should see the new reactive prop value');
        $this->assertEquals(5, $child->get('bootedValue'), 'booted() should see the new reactive prop value');
    }

    public function test_updating_hook_sees_old_value_and_updated_hook_sees_new_value_for_reactive_props()
    {
        Livewire::component('child-with-update-hooks', ChildWithUpdateHooks::class);

        $child = Livewire::test(ChildWithUpdateHooks::class, ['count' => 0]);

        // Simulate parent passing count=5 on next request
        SupportReactiveProps::$pendingChildParams[$child->id()] = ['count' => 5];
        $child->call('$refresh');

        $this->assertEquals(5, $child->get('count'));
        $this->assertEquals(0, $child->get('oldValueDuringUpdating'), 'updatingCount() should see the old value via $this->count');
        $this->assertEquals(5, $child->get('newValueDuringUpdated'), 'updatedCount() should see the new value via $this->count');
    }

    public function test_values_match_returns_false_when_either_value_cannot_be_json_encoded()
    {
        // Both NAN — would collide via crc32(false) without the guard...
        $this->assertFalse(SupportReactiveProps::valuesMatch(NAN, NAN));

        // Both INF — same risk...
        $this->assertFalse(SupportReactiveProps::valuesMatch(INF, INF));

        // One side encodable, one side not...
        $this->assertFalse(SupportReactiveProps::valuesMatch('hello', NAN));
        $this->assertFalse(SupportReactiveProps::valuesMatch(NAN, 'hello'));
    }

    public function test_values_match_returns_true_for_equal_scalars_and_arrays()
    {
        $this->assertTrue(SupportReactiveProps::valuesMatch('hello', 'hello'));
        $this->assertTrue(SupportReactiveProps::valuesMatch(42, 42));
        $this->assertTrue(SupportReactiveProps::valuesMatch(true, true));
        $this->assertTrue(SupportReactiveProps::valuesMatch(null, null));
        $this->assertTrue(SupportReactiveProps::valuesMatch([1, 2, 3], [1, 2, 3]));
        $this->assertTrue(SupportReactiveProps::valuesMatch(['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2]));
    }

    public function test_values_match_returns_false_for_different_scalars_and_arrays()
    {
        $this->assertFalse(SupportReactiveProps::valuesMatch('hello', 'world'));
        $this->assertFalse(SupportReactiveProps::valuesMatch(42, 43));
        $this->assertFalse(SupportReactiveProps::valuesMatch([1, 2, 3], [1, 2, 4]));
        $this->assertFalse(SupportReactiveProps::valuesMatch('5', 5));
    }

    public function test_values_match_ignores_loaded_relations_on_pending_model()
    {
        $article = Article::query()->first();
        $article->setRelation('author', Author::query()->first());

        $snapshotValue = [null, [
            'class' => Article::class,
            'key' => $article->getKey(),
            's' => 'mdl',
        ]];

        // Relations are not part of model identity on the wire (ModelSynth).
        $this->assertTrue(SupportReactiveProps::valuesMatch($snapshotValue, $article));
    }

    public function test_accessing_lazy_loaded_relation_on_reactive_model_does_not_throw()
    {
        $article = Article::first();

        $child = Livewire::test(new class extends Component {
            #[BaseReactive]
            public $article;

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $article->author->name }}</div>
                HTML;
            }
        }, ['article' => $article]);

        SupportReactiveProps::$pendingChildParams[$child->id()] = ['article' => $article];

        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), [
                'components' => [
                    [
                        'snapshot' => json_encode($child->snapshot),
                        'updates' => [],
                        'calls' => [
                            ['method' => '$refresh', 'params' => [], 'metadata' => []],
                        ],
                    ],
                ],
            ])->assertOk();

        $child->assertSee('Ghabriel');
    }

    public function test_accessing_eager_load_relation_on_reactive_model_does_not_throw()
    {
        $article = Article::with('author')->first();

        $child = Livewire::test(new class extends Component {
            #[BaseReactive]
            public $article;

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $article->author->name }}</div>
                HTML;
            }
        }, ['article' => $article]);

        SupportReactiveProps::$pendingChildParams[$child->id()] = ['article' => $article];

        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), [
                'components' => [
                    [
                        'snapshot' => json_encode($child->snapshot),
                        'updates' => [],
                        'calls' => [
                            ['method' => '$refresh', 'params' => [], 'metadata' => []],
                        ],
                    ],
                ],
            ])->assertOk();

        $child->assertSee('Ghabriel');
    }

    public function test_mutating_reactive_model_attributes_still_throws()
    {
        $this->expectException(CannotMutateReactivePropException::class);

        $article = Article::first();

        $child = Livewire::test(new class extends Component {
            #[BaseReactive]
            public $article;

            public function rename()
            {
                $this->article->title = 'Changed';
            }

            public function render()
            {
                return '<div></div>';
            }
        }, ['article' => $article]);

        SupportReactiveProps::$pendingChildParams[$child->id()] = ['article' => $article];

        $child->call('rename');
    }

    public function test_parent_passed_dirty_model_does_not_false_positive_when_child_does_nothing()
    {
        $article = Article::first();

        $article->title = 'Dirty from parent';

        $this->assertTrue($article->isDirty('title'));

        $child = Livewire::test(new class extends Component {
            #[BaseReactive]
            public $article;

            public function render()
            {
                return <<<'HTML'
                    <div>{{ $article->title }}</div>
                HTML;
            }
        }, ['article' => $article]);

        SupportReactiveProps::$pendingChildParams[$child->id()] = ['article' => $article];

        $snapshot = $child->snapshot;

        // Simulate a subsequent request where the parent again passes the
        // same (still dirty) model and the child only refreshes.
        $this->withHeaders(['X-Livewire' => 'true'])
            ->postJson(EndpointResolver::updatePath(), [
                'components' => [
                    [
                        'snapshot' => json_encode($snapshot),
                        'updates' => [],
                        'calls' => [
                            ['method' => '$refresh', 'params' => [], 'metadata' => []],
                        ],
                    ],
                ],
            ])->assertOk();

        $child->assertSee('Dirty from parent');
    }
}

class ChildWithLifecycleHooks extends Component
{
    #[BaseReactive]
    public $count;

    public $bootValue = 0;
    public $hydrateValue = 0;
    public $bootedValue = 0;

    public function boot()
    {
        $this->bootValue = $this->count;
    }

    public function hydrate()
    {
        $this->hydrateValue = $this->count;
    }

    public function booted()
    {
        $this->bootedValue = $this->count;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}

class ChildWithUpdateHooks extends Component
{
    #[BaseReactive]
    public $count;

    public $oldValueDuringUpdating = null;
    public $newValueDuringUpdated = null;

    public function updatingCount($value)
    {
        // $this->count should still be the OLD value at this point
        $this->oldValueDuringUpdating = $this->count;
    }

    public function updatedCount($value)
    {
        // $this->count should be the NEW value at this point
        $this->newValueDuringUpdated = $this->count;
    }

    public function render()
    {
        return '<div>{{ $count }}</div>';
    }
}

class Author extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'Ghabriel'],
    ];
}

class Article extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'First', 'author_id' => 1],
        ['id' => 2, 'title' => 'Second', 'author_id' => 1],
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}
