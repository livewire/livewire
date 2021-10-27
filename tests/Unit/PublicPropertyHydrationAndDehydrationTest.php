<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Exceptions\CorruptComponentPayloadException;
use Livewire\Exceptions\CannotBindToModelDataWithoutValidationRuleException;
use Livewire\HydrationMiddleware\HydratePublicProperties;

class PublicPropertyHydrationAndDehydrationTest extends TestCase
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
    }

    /** @test */
    public function an_eloquent_model_properties_with_deep_relations_and_single_relations_can_have_dirty_data_reapplied()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $model = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->first();

        $dirtyData = [
            'title' => 'oof',
            'name' => 'rab',
            'email' => 'zab',
            'posts' => [
                [
                    'title' => '1 Post',
                    'description' => 'Description 1 Post',
                    'content' => 'Content 1 Post',
                    'comments' => [
                        [],
                        [
                            'comment' => '2 Comment',
                            'author' => [
                                'name' => 'gniht'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $results = HydratePublicProperties::setDirtyData($model, $dirtyData);

        $this->assertEquals($model->title, 'oof');
        $this->assertEquals($model->name, 'rab');
        $this->assertEquals($model->email, 'zab');
        $this->assertEquals($model->posts[0]->title, '1 Post');
        $this->assertEquals($model->posts[0]->description, 'Description 1 Post');
        $this->assertEquals($model->posts[0]->content, 'Content 1 Post');
        $this->assertEquals($model->posts[0]->comments[1]->comment, '2 Comment');
        $this->assertEquals($model->posts[0]->comments[1]->author->name, 'gniht');
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
    public function array_rules_get_extracted_properly()
    {
        $rules = [
            'foo.lob.law.*',
            'foo.lob.law.*.blog',
        ];

        $expected = [
            'foo' => [
                'lob' => [
                    'law' => [
                        '*' => [
                            'blog',
                        ],
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

        $results = HydratePublicProperties::extractData($model->toArray(), HydratePublicProperties::processRules($rules)['author'], []);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function an_eloquent_model_properties_with_deep_relations_and_single_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $models = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->first();

        $rules = [
            'author.title',
            'author.email',
            'author.posts.*.title',
            'author.posts.*.comments.*.comment',
            'author.posts.*.comments.*.author.name',
        ];

        $expected = [
            'title' => 'foo',
            'email' => 'baz',
            'posts' => [
                [
                    'title' => 'Post 1',
                    'comments' => [
                        [
                            'comment' => 'Comment 1',
                            'author' => [
                                'name' => 'bar'
                            ],
                        ],
                        [
                            'comment' => 'Comment 2',
                            'author' => [
                                'name' => 'thing'
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Post 2',
                    'comments' => [],
                ],
            ],
        ];

        $results = HydratePublicProperties::extractData($models->toArray(), HydratePublicProperties::processRules($rules)['author']->toArray(), []);

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

        $results = HydratePublicProperties::extractData($models->toArray(), HydratePublicProperties::processRules($rules)['authors']->toArray(), []);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function an_eloquent_collection_properties_with_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);
        Post::create(['id' => 3, 'title' => 'Post 3', 'description' => 'Post 3 Description', 'content' => 'Post 3 Content', 'author_id' => 2]);
        Post::create(['id' => 4, 'title' => 'Post 4', 'description' => 'Post 4 Description', 'content' => 'Post 4 Content', 'author_id' => 2]);

        $models = Author::with('posts')->get();

        $rules = [
            'authors.*.title',
            'authors.*.email',
            'authors.*.posts.*.title',
        ];

        $expected = [
            [
                'title' => 'foo',
                'email' => 'baz',
                'posts' => [
                    ['title' => 'Post 1'],
                    ['title' => 'Post 2'],
                ],
            ],
            [
                'title' => 'sample',
                'email' => 'todo',
                'posts' => [
                    ['title' => 'Post 3'],
                    ['title' => 'Post 4'],
                ],
            ],
        ];

        $results = HydratePublicProperties::extractData($models->toArray(), HydratePublicProperties::processRules($rules)['authors']->toArray(), []);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function an_eloquent_collection_properties_with_deep_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);
        Comment::create(['id' => 3, 'comment' => 'Comment 3', 'post_id' => 2, 'author_id' => 1]);
        Comment::create(['id' => 4, 'comment' => 'Comment 4', 'post_id' => 2, 'author_id' => 2]);

        $models = Author::with(['posts', 'posts.comments'])->get();

        $rules = [
            'authors.*.title',
            'authors.*.email',
            'authors.*.posts.*.title',
            'authors.*.posts.*.comments.*.comment',
        ];

        $expected = [
            [
                'title' => 'foo',
                'email' => 'baz',
                'posts' => [
                    [
                        'title' => 'Post 1',
                        'comments' => [
                            ['comment' => 'Comment 1'],
                            ['comment' => 'Comment 2'],
                        ],
                    ],
                    [
                        'title' => 'Post 2',
                        'comments' => [
                            ['comment' => 'Comment 3'],
                            ['comment' => 'Comment 4'],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'sample',
                'email' => 'todo',
                'posts' => [],
            ],
        ];

        $results = HydratePublicProperties::extractData($models->toArray(), HydratePublicProperties::processRules($rules)['authors']->toArray(), []);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function an_eloquent_collection_properties_with_deep_relations_and_single_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $models = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->get();

        $rules = [
            'authors.*.title',
            'authors.*.email',
            'authors.*.posts.*.title',
            'authors.*.posts.*.comments.*.comment',
            'authors.*.posts.*.comments.*.author.name',
        ];

        $expected = [
            [
                'title' => 'foo',
                'email' => 'baz',
                'posts' => [
                    [
                        'title' => 'Post 1',
                        'comments' => [
                            [
                                'comment' => 'Comment 1',
                                'author' => [
                                    'name' => 'bar'
                                ],
                            ],
                            [
                                'comment' => 'Comment 2',
                                'author' => [
                                    'name' => 'thing'
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Post 2',
                        'comments' => [],
                    ],
                ],
            ],
            [
                'title' => 'sample',
                'email' => 'todo',
                'posts' => [],
            ],
        ];

        $results = HydratePublicProperties::extractData($models->toArray(), HydratePublicProperties::processRules($rules)['authors']->toArray(), []);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function an_eloquent_collection_properties_with_deep_relations_with_skipped_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $models = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->get();

        $rules = [
            'authors.*.title',
            'authors.*.email',
            'authors.*.posts.*.comments.*.comment',
            'authors.*.posts.*.comments.*.author.name',
        ];

        $expected = [
            [
                'title' => 'foo',
                'email' => 'baz',
                'posts' => [
                    [
                        'comments' => [
                            [
                                'comment' => 'Comment 1',
                                'author' => [
                                    'name' => 'bar'
                                ],
                            ],
                            [
                                'comment' => 'Comment 2',
                                'author' => [
                                    'name' => 'thing'
                                ],
                            ],
                        ],
                    ],
                    [
                        'comments' => [],
                    ],
                ],
            ],
            [
                'title' => 'sample',
                'email' => 'todo',
                'posts' => [],
            ],
        ];

        $results = HydratePublicProperties::extractData($models->toArray(), HydratePublicProperties::processRules($rules)['authors']->toArray(), []);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function it_does_not_throw_error_if_relation_is_not_loaded()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);
        Comment::create(['id' => 3, 'comment' => 'Comment 3', 'post_id' => 2, 'author_id' => 1]);
        Comment::create(['id' => 4, 'comment' => 'Comment 4', 'post_id' => 2, 'author_id' => 2]);

        $models = Author::with(['posts'])->get();

        $rules = [
            'authors.*.title',
            'authors.*.email',
            'authors.*.posts.*.title',
            'authors.*.posts.*.comments.*.comment',
        ];

        $expected = [
            [
                'title' => 'foo',
                'email' => 'baz',
                'posts' => [
                    [
                        'title' => 'Post 1',
                        'comments' => [],
                    ],
                    [
                        'title' => 'Post 2',
                        'comments' => [],
                    ],
                ],
            ],
            [
                'title' => 'sample',
                'email' => 'todo',
                'posts' => [],
            ],
        ];

        $results = HydratePublicProperties::extractData($models->toArray(), HydratePublicProperties::processRules($rules)['authors']->toArray(), []);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function it_does_not_throw_error_if_model_property_does_not_exist()
    {
        $model = Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);

        $rules = [
            'author.title',
            'author.foo',
            'author.email',
        ];

        $expected = [
            'title' => 'foo',
            'email' => 'baz',
            'foo' => null,
        ];

        $results = HydratePublicProperties::extractData($model->toArray(), HydratePublicProperties::processRules($rules)['author'], []);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function it_serialises_properties_from_model_that_has_not_been_persisted()
    {
        $model = Author::make();

        $rules = [
            'author.name',
        ];

        $expected = [
            'name' => null,
        ];

        $results = HydratePublicProperties::extractData($model->toArray(), HydratePublicProperties::processRules($rules)['author'], []);

        $this->assertEquals($expected, $results);
    }
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
