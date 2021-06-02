<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Livewire\Exceptions\CorruptComponentPayloadException;
use Livewire\Exceptions\CannotBindToModelDataWithoutValidationRuleException;
use Livewire\HydrationMiddleware\HydratePublicProperties;

class PublicPropertyHydrationTest extends TestCase
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
    public function an_eloquent_model_properties_with_deep_relations_and_single_relations_can_be_serialised()
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

        ray($model->toArray());

        $this->assertEquals($model->title, 'oof');
        $this->assertEquals($model->name, 'rab');
        $this->assertEquals($model->email, 'zab');
        $this->assertEquals($model->posts[0]->title, '1 Post');
        $this->assertEquals($model->posts[0]->description, 'Description 1 Post');
        $this->assertEquals($model->posts[0]->content, 'Content 1 Post');
        $this->assertEquals($model->posts[0]->comments[1]->comment, '2 Comment');
        $this->assertEquals($model->posts[0]->comments[1]->author->name, 'gniht');
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
