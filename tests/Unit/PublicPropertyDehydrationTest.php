<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Exceptions\CorruptComponentPayloadException;
use Livewire\Exceptions\CannotBindToModelDataWithoutValidationRuleException;
use Livewire\HydrationMiddleware\HydratePublicProperties;

class PublicPropertyDehydrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('authors', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('posts', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('description');
            $table->string('content');
            $table->foreignId('author_id');
            $table->timestamps();
        });

        Schema::create('comments', function ($table) {
            $table->bigIncrements('id');
            $table->string('comment');
            $table->foreignId('post_id');
            $table->foreignId('author_id');
            $table->timestamps();
        });

        // $model = ModelForSerialization::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);

        // ModelForSerialization::create(['id' => 1, 'title' => 'foo']);
        // ModelForSerialization::create(['id' => 2, 'title' => 'bar']);

        // $models = ModelForSerialization::all();
    }

    /** @test */
    public function rules_get_extracted_properly()
    {
        $rules = [
            'author.title',
            'author.email',
            'author.posts.*.title',
            'author.posts.*.description',
            'authors.*.title',
            'authors.*.email',
            'authors.*.posts.*.title',
            'authors.*.posts.*.content',
            'posts.*.title',
            'posts.*.content',
            'posts.*.author.name',
        ];

        $expected = [
            'author' => [
                'title',
                'email',
                'posts' => [
                    '*' => [
                        'title',
                        'description',
                    ],
                ],
            ],
            'authors' => [
                '*' => [
                    'title',
                    'email',
                    'posts' => [
                        '*' => [
                            'title',
                            'content',
                        ],
                    ],
                ],
            ],
            'posts' => [
                '*' => [
                    'title',
                    'content',
                    'author' => [
                        'name',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, HydratePublicProperties::processRules($rules)->toArray());
    }

    /** @test */
    public function an_eloquent_model_properties_can_be_serialised()
    {
        $model = Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);

        $rules = [
            'author.title',
            'author.email',
        ];

        $expected = [
            'title' => 'foo',
            'email' => 'baz',
        ];

        ray()->clearScreen();

        $results = HydratePublicProperties::extractData($model->toArray(), HydratePublicProperties::processRules($rules)['author'], []);

        ray($results);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function an_eloquent_collection_properties_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        $models = Author::all();

        $rules = [
            'authors.*.title',
            'authors.*.email',
        ];

        $expected = [
            [
                'title' => 'foo',
                'email' => 'baz',
            ],
            [
                'title' => 'sample',
                'email' => 'todo',
            ],
        ];

        ray()->clearScreen();

        $results = HydratePublicProperties::extractData($models->toArray(), HydratePublicProperties::processRules($rules)['authors']->toArray(), []);

        ray('Results', $results);

        $this->assertEquals($expected, $results);
    }

    // /** @test */
    // public function an_eloquent_collection_properties_with_relations_can_be_serialised()
    // {
    //     Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
    //     Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

    //     Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
    //     Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);
    //     Post::create(['id' => 3, 'title' => 'Post 3', 'description' => 'Post 3 Description', 'content' => 'Post 3 Content', 'author_id' => 2]);
    //     Post::create(['id' => 4, 'title' => 'Post 4', 'description' => 'Post 4 Description', 'content' => 'Post 4 Content', 'author_id' => 2]);

    //     $models = Author::with('posts')->get();

    //     $rules = [
    //         'users.*.title',
    //         'users.*.email',
    //         'users.*.posts.*.title',
    //     ];

    //     $expected = [
    //         [
    //             'title' => 'foo',
    //             'email' => 'baz',
    //             'posts' => [
    //                 ['title' => 'Post 1'],
    //                 ['title' => 'Post 2'],
    //             ],
    //         ],
    //         [
    //             'title' => 'sample',
    //             'email' => 'todo',
    //             'posts' => [
    //                 ['title' => 'Post 3'],
    //                 ['title' => 'Post 4'],
    //             ],
    //         ],
    //     ];

    //     $this->assertEquals($expected, HydratePublicProperties::filterData2($models, $rules));
    // }

    // /** @test */
    // public function an_eloquent_collection_properties_with_deep_relations_can_be_serialised()
    // {
    //     Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
    //     Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

    //     Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
    //     Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

    //     Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
    //     Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);
    //     Comment::create(['id' => 3, 'comment' => 'Comment 3', 'post_id' => 2, 'author_id' => 1]);
    //     Comment::create(['id' => 4, 'comment' => 'Comment 4', 'post_id' => 2, 'author_id' => 2]);

    //     $models = Author::with('posts')->get();

    //     $rules = [
    //         'users.*.title',
    //         'users.*.email',
    //         'users.*.posts.*.title',
    //         'users.*.posts.*.comments.*.comment',
    //     ];

    //     $expected = [
    //         [
    //             'title' => 'foo',
    //             'email' => 'baz',
    //             'posts' => [
    //                 [
    //                     'title' => 'Post 1',
    //                     'comments' => [
    //                         ['comment' => 'Comment 1'],
    //                         ['comment' => 'Comment 2'],
    //                     ],
    //                 ],
    //                 [
    //                     'title' => 'Post 2',
    //                     'comments' => [
    //                         ['comment' => 'Comment 3'],
    //                         ['comment' => 'Comment 4'],
    //                     ],
    //                 ],
    //             ],
    //         ],
    //         [
    //             'title' => 'sample',
    //             'email' => 'todo',
    //             'posts' => [],
    //         ],
    //     ];

    //     $this->assertEquals($expected, HydratePublicProperties::filterData2($models, $rules));
    // }

    // /** @test */
    // public function an_eloquent_collection_properties_with_deep_relations_and_single_relations_can_be_serialised()
    // {
    //     Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
    //     Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

    //     Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
    //     Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

    //     Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
    //     Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

    //     $models = Author::with('posts')->get();

    //     $rules = [
    //         'users.*.title',
    //         'users.*.email',
    //         'users.*.posts.*.title',
    //         'users.*.posts.*.comments.*.comment',
    //         'users.*.posts.*.comments.*.author.name',
    //     ];

    //     $expected = [
    //         [
    //             'title' => 'foo',
    //             'email' => 'baz',
    //             'posts' => [
    //                 [
    //                     'title' => 'Post 1',
    //                     'comments' => [
    //                         [
    //                             'comment' => 'Comment 1',
    //                             'author' => [
    //                                 'name' => 'bar'
    //                             ],
    //                         ],
    //                         [
    //                             'comment' => 'Comment 2',
    //                             'author' => [
    //                                 'name' => 'thing'
    //                             ],
    //                         ],
    //                     ],
    //                 ],
    //                 [
    //                     'title' => 'Post 2',
    //                     'comments' => [],
    //                 ],
    //             ],
    //         ],
    //         [
    //             'title' => 'sample',
    //             'email' => 'todo',
    //             'posts' => [],
    //         ],
    //     ];

    //     $this->assertEquals($expected, HydratePublicProperties::filterData2($models, $rules));
    // }
}

class Author extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Post extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

// class ComponentWithModelPublicProperty extends Component
// {
//     public $model;

//     public function mount(ModelForSerialization $model)
//     {
//         $this->model = $model;
//     }

//     public function refresh() {}

//     public function deleteAndRemoveModel()
//     {
//         $this->model->delete();

//         $this->model = null;
//     }

//     public function render()
//     {
//         return view('model-arrow-title-view');
//     }
// }

// class ComponentWithModelsPublicProperty extends Component
// {
//     public $models;

//     public function mount($models)
//     {
//         $this->models = $models;
//     }

//     public function refresh() {}

//     public function render()
//     {
//         return view('foreach-models-arrow-title-view');
//     }
// }
