<?php

namespace Livewire\Features\SupportReactiveProps;

use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    function test_can_pass_prop_to_child_component()
    {
        Livewire::test([new class extends Component {
            public $foo = 'bar';

            public function render() {
                return '<div><livewire:child :oof="$foo" /></div>';
            }
        }, 'child' => new class extends Component {
            public $oof;

            public function render() {
                return '<div>{{ $oof }}</div>';
            }

        }])
        ->assertSee('bar');
    }

    function test_can_change_reactive_prop_in_child_component()
    {
        $this->markTestSkipped('Unit testing child components isnt supported yet');

        $component = Livewire::test([new class extends Component {
            public $todos = [];

            public function render() {
                return '<div><livewire:child :todos="$todos" /></div>';
            }
        }, 'child' => new class extends Component {
            #[Prop(reactive: true)]
            public $todos;

            public function render() {
                return '<div>Count: {{ count($todos) }}.</div>';
            }
        }]);

        $component->assertSee('Count: 0.');

        $component->set('todos', ['todo 1']);
        $component->assertSee('Count: 1.');

        $component->set('todos', ['todo 1', 'todo 2', 'todo 3']);
        $component->assertSee('Count: 3.');
    }

    function test_accessing_lazy_loaded_relation_on_reactive_model_does_not_throw()
    {
        $article = ReactiveArticle::query()->first();

        Livewire::test(new class extends Component {
            #[BaseReactive]
            public $article;

            public function render()
            {
                return '<div>{{ $article->author->name }}</div>';
            }
        }, ['article' => $article])
            ->assertSee('Ghabriel');
    }

    function test_mutating_reactive_model_attributes_still_throws()
    {
        $this->expectException(CannotMutateReactivePropException::class);

        $article = ReactiveArticle::query()->first();

        Livewire::test(new class extends Component {
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
        }, ['article' => $article])
            ->call('rename');
    }
}

class ReactiveAuthor extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['id' => 1, 'name' => 'Ghabriel'],
    ];
}

class ReactiveArticle extends \Illuminate\Database\Eloquent\Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['id' => 1, 'title' => 'First', 'author_id' => 1],
    ];

    public function author()
    {
        return $this->belongsTo(ReactiveAuthor::class, 'author_id');
    }
}
