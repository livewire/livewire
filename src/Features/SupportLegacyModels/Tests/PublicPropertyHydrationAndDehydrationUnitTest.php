<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Livewire\Livewire;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Tests\TestComponent;

class PublicPropertyHydrationAndDehydrationUnitTest extends \Tests\TestCase
{
    use Concerns\EnableLegacyModels;

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
            $table->foreignId('author_id')->nullable();
            $table->timestamps();
        });

        Schema::create('comments', function ($table) {
            $table->bigIncrements('id');
            $table->string('comment');
            $table->foreignId('post_id');
            $table->foreignId('author_id');
            $table->timestamps();
        });

        Schema::create('other_comments', function ($table) {
            $table->bigIncrements('id');
            $table->string('comment');
            $table->foreignId('post_id');
            $table->foreignId('author_id');
            $table->timestamps();
        });
    }

    public function test_it_uses_class_name_if_laravels_morph_map_not_available_when_dehydrating()
    {
        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);

        $component = Livewire::test(PostComponent::class);
        $this->assertEquals('Livewire\Features\SupportLegacyModels\Tests\Post', $component->snapshot['data']['post'][1]['class']);
    }

    public function test_it_uses_class_name_if_laravels_morph_map_not_available_when_hydrating()
    {
        $post = Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);

        $post = $post->fresh();

        Livewire::test(PostComponent::class)
            ->call('$refresh')
            ->assertSet('post', $post);
    }

    public function test_it_uses_laravels_morph_map_instead_of_class_name_if_available_when_dehydrating()
    {
        $post = Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);

        Relation::morphMap([
            'post' => Post::class,
        ]);

        $component = Livewire::test(PostComponent::class);

        $this->assertEquals('post', $component->snapshot['data']['post'][1]['class']);
    }

    public function test_it_uses_laravels_morph_map_instead_of_class_name_if_available_when_hydrating()
    {
        $post = Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);

        $post = $post->fresh();

        Relation::morphMap([
            'post' => Post::class,
        ]);

        Livewire::test(PostComponent::class)
            ->call('$refresh')
            ->assertSet('post', $post);
    }

    public function test_it_does_not_trigger_ClassMorphViolationException_when_morh_map_is_enforced()
    {
        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content']);

        // reset morph
        Relation::morphMap([], false);
        Relation::requireMorphMap();

        $component = Livewire::test(PostComponent::class);
        $this->assertEquals('Livewire\Features\SupportLegacyModels\Tests\Post', $component->snapshot['data']['post'][1]['class']);
        Relation::requireMorphMap(false);
    }

    public function test_an_eloquent_model_properties_with_deep_relations_and_single_relations_can_have_dirty_data_reapplied()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $model = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->first();

        $component = Livewire::test(ModelsComponent::class, ['model' => $model])
            ->set('model.title', 'oof')
            ->set('model.name', 'rab')
            ->set('model.email', 'zab')
            ->set('model.posts.0.title', '1 Post')
            ->set('model.posts.0.description', 'Description 1 Post')
            ->set('model.posts.0.content', 'Content 1 Post')
            ->set('model.posts.0.comments.1.comment', '2 Comment')
            ->set('model.posts.0.comments.1.author.name', 'gniht');

        $updatedModel = $component->get('model');

        $this->assertEquals('oof', $updatedModel->title);
        $this->assertEquals('rab', $updatedModel->name);
        $this->assertEquals('zab', $updatedModel->email);
        $this->assertEquals('1 Post', $updatedModel->posts[0]->title,);
        $this->assertEquals('Description 1 Post', $updatedModel->posts[0]->description);
        $this->assertEquals('Content 1 Post', $updatedModel->posts[0]->content);
        $this->assertEquals('2 Comment', $updatedModel->posts[0]->comments[1]->comment);
        $this->assertEquals('gniht', $updatedModel->posts[0]->comments[1]->author->name);
    }

    public function test_an_eloquent_model_properties_with_deep_relations_and_multiword_relations_can_have_dirty_data_reapplied()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        OtherComment::create(['id' => 1, 'comment' => 'Other Comment 1', 'post_id' => 1, 'author_id' => 1]);
        OtherComment::create(['id' => 2, 'comment' => 'Other Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $model = Author::with(['posts', 'posts.comments', 'posts.comments.author', 'posts.otherComments', 'posts.otherComments.author'])->first();

        $component = Livewire::test(ModelsComponent::class, ['model' => $model])
            ->set('model.title', 'oof')
            ->set('model.name', 'rab')
            ->set('model.email', 'zab')
            ->set('model.posts.0.title', '1 Post')
            ->set('model.posts.0.description', 'Description 1 Post')
            ->set('model.posts.0.content', 'Content 1 Post')
            ->set('model.posts.0.comments.1.comment', '2 Comment')
            ->set('model.posts.0.comments.1.author.name', 'gniht')
            ->set('model.posts.0.otherComments.1.comment', '2 Other Comment');

        $updatedModel = $component->get('model');

        $this->assertEquals('oof', $updatedModel->title);
        $this->assertEquals('rab', $updatedModel->name);
        $this->assertEquals('zab', $updatedModel->email);
        $this->assertEquals('1 Post', $updatedModel->posts[0]->title,);
        $this->assertEquals('Description 1 Post', $updatedModel->posts[0]->description);
        $this->assertEquals('Content 1 Post', $updatedModel->posts[0]->content);
        $this->assertEquals('2 Comment', $updatedModel->posts[0]->comments[1]->comment);
        $this->assertEquals('gniht', $updatedModel->posts[0]->comments[1]->author->name);
        $this->assertEquals('2 Other Comment', $updatedModel->posts[0]->otherComments[1]->comment);
    }

    public function test_an_eloquent_model_with_a_properties_dirty_data_set_to_an_empty_array_gets_hydrated_properly()
    {
        $model = new Author();

        $component = Livewire::test(ModelsComponent::class, ['model' => $model])
            ->set('model.name', []);

        $updatedModel = $component->get('model');

        $this->assertEquals([], $updatedModel->name);
    }

    public function test_an_eloquent_model_properties_can_be_serialised()
    {
        $model = Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);

        $rules = [
            'model.title' => '',
            'model.email' => '',
        ];

        $expected = [
            'title' => 'foo',
            'email' => 'baz',
        ];

        $component = Livewire::test(ModelsComponent::class, ['model' => $model, 'rules' => $rules]);

        $results = $component->snapshot['data']['model'][0];

        $this->assertEquals($expected, $results);
    }

    public function test_an_eloquent_model_properties_with_deep_relations_and_single_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $model = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->first();

        $rules = [
            'model.title' => '',
            'model.email' => '',
            'model.posts.*.title' => '',
            'model.posts.*.comments.*.comment' => '',
            'model.posts.*.comments.*.author.name' => '',
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

        $component = Livewire::test(ModelsComponent::class, ['model' => $model, 'rules' => $rules]);

        $results = $component->snapshot['data']['model'][0];

        $this->assertEquals($expected['title'], $results['title']);
        $this->assertEquals($expected['email'], $results['email']);
        $this->assertEquals($expected['posts'][0]['title'], $results['posts'][0][0][0]['title']);
        $this->assertEquals($expected['posts'][0]['comments'][0]['comment'], $results['posts'][0][0][0]['comments'][0][0][0]['comment']);
        $this->assertEquals($expected['posts'][0]['comments'][0]['author']['name'], $results['posts'][0][0][0]['comments'][0][0][0]['author'][0]['name']);
        $this->assertEquals($expected['posts'][1]['title'], $results['posts'][0][1][0]['title']);
        $this->assertEquals($expected['posts'][1]['comments'], $results['posts'][0][1][0]['comments'][0]);
    }

    public function test_an_eloquent_model_properties_with_deep_relations_and_multiword_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        OtherComment::create(['id' => 1, 'comment' => 'Other Comment 1', 'post_id' => 1, 'author_id' => 1]);
        OtherComment::create(['id' => 2, 'comment' => 'Other Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $model = Author::with(['posts', 'posts.comments', 'posts.comments.author', 'posts.otherComments', 'posts.otherComments.author'])->first();

        $rules = [
            'model.title' => '',
            'model.email' => '',
            'model.posts.*.title' => '',
            'model.posts.*.comments.*.comment' => '',
            'model.posts.*.comments.*.author.name' => '',
            'model.posts.*.otherComments.*.comment' => '',
            'model.posts.*.otherComments.*.author.name' => '',
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
                    'otherComments' => [
                        [
                            'comment' => 'Other Comment 1',
                            'author' => [
                                'name' => 'bar'
                            ],
                        ],
                        [
                            'comment' => 'Other Comment 2',
                            'author' => [
                                'name' => 'thing'
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Post 2',
                    'comments' => [],
                    'otherComments' => [],
                ],
            ],
        ];

        $component = Livewire::test(ModelsComponent::class, ['model' => $model, 'rules' => $rules]);

        $results = $component->snapshot['data']['model'][0];

        $this->assertEquals($expected['title'], $results['title']);
        $this->assertEquals($expected['email'], $results['email']);
        $this->assertEquals($expected['posts'][0]['title'], $results['posts'][0][0][0]['title']);
        $this->assertEquals($expected['posts'][0]['comments'][0]['comment'], $results['posts'][0][0][0]['comments'][0][0][0]['comment']);
        $this->assertEquals($expected['posts'][0]['comments'][0]['author']['name'], $results['posts'][0][0][0]['comments'][0][0][0]['author'][0]['name']);
        $this->assertEquals($expected['posts'][0]['otherComments'][0]['comment'], $results['posts'][0][0][0]['otherComments'][0][0][0]['comment']);
        $this->assertEquals($expected['posts'][0]['otherComments'][0]['author']['name'], $results['posts'][0][0][0]['otherComments'][0][0][0]['author'][0]['name']);
        $this->assertEquals($expected['posts'][1]['title'], $results['posts'][0][1][0]['title']);
        $this->assertEquals($expected['posts'][1]['comments'], $results['posts'][0][1][0]['comments'][0]);
    }

    public function test_an_eloquent_collection_properties_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        $models = Author::all();

        $rules = [
            'models.*.title' => '',
            'models.*.email' => '',
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

        $component = Livewire::test(ModelsComponent::class, ['models' => $models, 'rules' => $rules]);

        $results = $component->snapshot['data']['models'][0];

        $this->assertEquals($expected[0], $results[0][0]);
        $this->assertEquals($expected[1], $results[1][0]);
    }

    public function test_an_eloquent_collection_properties_with_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);
        Post::create(['id' => 3, 'title' => 'Post 3', 'description' => 'Post 3 Description', 'content' => 'Post 3 Content', 'author_id' => 2]);
        Post::create(['id' => 4, 'title' => 'Post 4', 'description' => 'Post 4 Description', 'content' => 'Post 4 Content', 'author_id' => 2]);

        $models = Author::with('posts')->get();

        $rules = [
            'models.*.title' => '',
            'models.*.email' => '',
            'models.*.posts.*.title' => '',
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

        $component = Livewire::test(ModelsComponent::class, ['models' => $models, 'rules' => $rules]);

        $results = $component->snapshot['data']['models'][0];

        $this->assertEquals($expected[0]['title'], $results[0][0]['title']);
        $this->assertEquals($expected[0]['email'], $results[0][0]['email']);
        $this->assertEquals($expected[0]['posts'][0]['title'], $results[0][0]['posts'][0][0][0]['title']);
        $this->assertEquals($expected[0]['posts'][1]['title'], $results[0][0]['posts'][0][1][0]['title']);
        $this->assertEquals($expected[1]['title'], $results[1][0]['title']);
        $this->assertEquals($expected[1]['email'], $results[1][0]['email']);
        $this->assertEquals($expected[1]['posts'][0]['title'], $results[1][0]['posts'][0][0][0]['title']);
        $this->assertEquals($expected[1]['posts'][1]['title'], $results[1][0]['posts'][0][1][0]['title']);
    }

    public function test_an_eloquent_collection_properties_with_deep_relations_can_be_serialised()
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
            'models.*.title' => '',
            'models.*.email' => '',
            'models.*.posts.*.title' => '',
            'models.*.posts.*.comments.*.comment' => '',
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

        $component = Livewire::test(ModelsComponent::class, ['models' => $models, 'rules' => $rules]);

        $results = $component->snapshot['data']['models'][0];

        $this->assertEquals($expected[0]['title'], $results[0][0]['title']);
        $this->assertEquals($expected[0]['email'], $results[0][0]['email']);
        $this->assertEquals($expected[0]['posts'][0]['title'], $results[0][0]['posts'][0][0][0]['title']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][0]['comment'], $results[0][0]['posts'][0][0][0]['comments'][0][0][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][1]['comment'], $results[0][0]['posts'][0][0][0]['comments'][0][1][0]['comment']);
        $this->assertEquals($expected[0]['posts'][1]['title'], $results[0][0]['posts'][0][1][0]['title']);
        $this->assertEquals($expected[0]['posts'][1]['comments'][0]['comment'], $results[0][0]['posts'][0][1][0]['comments'][0][0][0]['comment']);
        $this->assertEquals($expected[0]['posts'][1]['comments'][1]['comment'], $results[0][0]['posts'][0][1][0]['comments'][0][1][0]['comment']);

        $this->assertEquals($expected[1]['title'], $results[1][0]['title']);
        $this->assertEquals($expected[1]['email'], $results[1][0]['email']);
        $this->assertEquals($expected[1]['posts'], $results[1][0]['posts'][0]);
    }

    public function test_an_eloquent_collection_properties_with_deep_relations_and_single_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $models = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->get();

        $rules = [
            'models.*.title' => '',
            'models.*.email' => '',
            'models.*.posts.*.title' => '',
            'models.*.posts.*.comments.*.comment' => '',
            'models.*.posts.*.comments.*.author.name' => '',
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

        $component = Livewire::test(ModelsComponent::class, ['models' => $models, 'rules' => $rules]);

        $results = $component->snapshot['data']['models'][0];

        $this->assertEquals($expected[0]['title'], $results[0][0]['title']);
        $this->assertEquals($expected[0]['email'], $results[0][0]['email']);
        $this->assertEquals($expected[0]['posts'][0]['title'], $results[0][0]['posts'][0][0][0]['title']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][0]['comment'], $results[0][0]['posts'][0][0][0]['comments'][0][0][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][0]['author']['name'], $results[0][0]['posts'][0][0][0]['comments'][0][0][0]['author'][0]['name']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][1]['comment'], $results[0][0]['posts'][0][0][0]['comments'][0][1][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][1]['author']['name'], $results[0][0]['posts'][0][0][0]['comments'][0][1][0]['author'][0]['name']);
        $this->assertEquals($expected[0]['posts'][1]['title'], $results[0][0]['posts'][0][1][0]['title']);
        $this->assertEquals($expected[0]['posts'][1]['comments'], $results[0][0]['posts'][0][1][0]['comments'][0]);

        $this->assertEquals($expected[1]['title'], $results[1][0]['title']);
        $this->assertEquals($expected[1]['email'], $results[1][0]['email']);
        $this->assertEquals($expected[1]['posts'], $results[1][0]['posts'][0]);
    }

    public function test_an_eloquent_collection_properties_with_deep_relations_and_multiword_relations_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        OtherComment::create(['id' => 1, 'comment' => 'Other Comment 1', 'post_id' => 1, 'author_id' => 1]);
        OtherComment::create(['id' => 2, 'comment' => 'Other Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $models = Author::with(['posts', 'posts.comments', 'posts.comments.author', 'posts.otherComments', 'posts.otherComments.author'])->get();

        $rules = [
            'models.*.title' => '',
            'models.*.email' => '',
            'models.*.posts.*.title' => '',
            'models.*.posts.*.comments.*.comment' => '',
            'models.*.posts.*.comments.*.author.name' => '',
            'models.*.posts.*.otherComments.*.comment' => '',
            'models.*.posts.*.otherComments.*.author.name' => '',
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
                        'otherComments' => [
                            [
                                'comment' => 'Other Comment 1',
                                'author' => [
                                    'name' => 'bar'
                                ],
                            ],
                            [
                                'comment' => 'Other Comment 2',
                                'author' => [
                                    'name' => 'thing'
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => 'Post 2',
                        'comments' => [],
                        'otherComments' => [],
                    ],
                ],
            ],
            [
                'title' => 'sample',
                'email' => 'todo',
                'posts' => [],
            ],
        ];

        $component = Livewire::test(ModelsComponent::class, ['models' => $models, 'rules' => $rules]);

        $results = $component->snapshot['data']['models'][0];

        $this->assertEquals($expected[0]['title'], $results[0][0]['title']);
        $this->assertEquals($expected[0]['email'], $results[0][0]['email']);
        $this->assertEquals($expected[0]['posts'][0]['title'], $results[0][0]['posts'][0][0][0]['title']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][0]['comment'], $results[0][0]['posts'][0][0][0]['comments'][0][0][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][0]['author']['name'], $results[0][0]['posts'][0][0][0]['comments'][0][0][0]['author'][0]['name']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][1]['comment'], $results[0][0]['posts'][0][0][0]['comments'][0][1][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][1]['author']['name'], $results[0][0]['posts'][0][0][0]['comments'][0][1][0]['author'][0]['name']);
        $this->assertEquals($expected[0]['posts'][0]['otherComments'][0]['comment'], $results[0][0]['posts'][0][0][0]['otherComments'][0][0][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['otherComments'][0]['author']['name'], $results[0][0]['posts'][0][0][0]['otherComments'][0][0][0]['author'][0]['name']);
        $this->assertEquals($expected[0]['posts'][0]['otherComments'][1]['comment'], $results[0][0]['posts'][0][0][0]['otherComments'][0][1][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['otherComments'][1]['author']['name'], $results[0][0]['posts'][0][0][0]['otherComments'][0][1][0]['author'][0]['name']);
        $this->assertEquals($expected[0]['posts'][1]['title'], $results[0][0]['posts'][0][1][0]['title']);
        $this->assertEquals($expected[0]['posts'][1]['comments'], $results[0][0]['posts'][0][1][0]['comments'][0]);

        $this->assertEquals($expected[1]['title'], $results[1][0]['title']);
        $this->assertEquals($expected[1]['email'], $results[1][0]['email']);
        $this->assertEquals($expected[1]['posts'], $results[1][0]['posts'][0]);
    }

    public function test_an_eloquent_collection_properties_with_deep_relations_with_skipped_intermediate_relations_rules_can_be_serialised()
    {
        Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);
        Author::create(['id' => 2, 'title' => 'sample', 'name' => 'thing', 'email' => 'todo']);

        Post::create(['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1]);
        Post::create(['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]);

        Comment::create(['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1]);
        Comment::create(['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]);

        $models = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->get();

        $rules = [
            'models.*.title' => '',
            'models.*.email' => '',
            'models.*.posts.*.comments.*.comment' => '',
            'models.*.posts.*.comments.*.author.name' => '',
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

        $component = Livewire::test(ModelsComponent::class, ['models' => $models, 'rules' => $rules]);

        $results = $component->snapshot['data']['models'][0];

        $this->assertEquals($expected[0]['title'], $results[0][0]['title']);
        $this->assertEquals($expected[0]['email'], $results[0][0]['email']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][0]['comment'], $results[0][0]['posts'][0][0][0]['comments'][0][0][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][0]['author']['name'], $results[0][0]['posts'][0][0][0]['comments'][0][0][0]['author'][0]['name']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][1]['comment'], $results[0][0]['posts'][0][0][0]['comments'][0][1][0]['comment']);
        $this->assertEquals($expected[0]['posts'][0]['comments'][1]['author']['name'], $results[0][0]['posts'][0][0][0]['comments'][0][1][0]['author'][0]['name']);
        $this->assertEquals($expected[0]['posts'][1]['comments'], $results[0][0]['posts'][0][1][0]['comments'][0]);

        $this->assertEquals($expected[1]['title'], $results[1][0]['title']);
        $this->assertEquals($expected[1]['email'], $results[1][0]['email']);
        $this->assertEquals($expected[1]['posts'], $results[1][0]['posts'][0]);
    }

    public function test_it_does_not_throw_error_if_relation_is_not_loaded()
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
            'models.*.title' => '',
            'models.*.email' => '',
            'models.*.posts.*.title' => '',
            'models.*.posts.*.comments.*.comment' => '',
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

        $component = Livewire::test(ModelsComponent::class, ['models' => $models, 'rules' => $rules]);

        $results = $component->snapshot['data']['models'][0];

        $this->assertEquals($expected[0]['title'], $results[0][0]['title']);
        $this->assertEquals($expected[0]['email'], $results[0][0]['email']);
        $this->assertEquals($expected[0]['posts'][0]['title'], $results[0][0]['posts'][0][0][0]['title']);
        $this->assertArrayHasKey('comments', $results[0][0]['posts'][0][0][0]);

        $this->assertEquals($expected[0]['posts'][1]['title'], $results[0][0]['posts'][0][1][0]['title']);
        $this->assertArrayHasKey('comments', $results[0][0]['posts'][0][1][0]);

        $this->assertEquals($expected[1]['title'], $results[1][0]['title']);
        $this->assertEquals($expected[1]['email'], $results[1][0]['email']);
        $this->assertEquals($expected[1]['posts'], $results[1][0]['posts'][0]);
    }

    public function test_it_does_not_throw_error_if_model_property_does_not_exist()
    {
        // @todo: Review this, as it's not quite correct, key "foo" should be sent to the front end, even if not set, to match V2 functionality
        $model = Author::create(['id' => 1, 'title' => 'foo', 'name' => 'bar', 'email' => 'baz']);

        $rules = [
            'model.title' => '',
            'model.foo' => '',
            'model.email' => '',
        ];

        $expected = [
            'title' => 'foo',
            'email' => 'baz',
            'foo' => null,
        ];

        $component = Livewire::test(ModelsComponent::class, ['model' => $model, 'rules' => $rules]);

        $results = $component->snapshot['data']['model'][0];

        $this->assertEquals($expected['title'], $results['title']);
        $this->assertEquals($expected['email'], $results['email']);
        $this->assertEquals($expected['foo'], $results['foo']);
    }

    public function test_it_serialises_properties_from_model_that_has_not_been_persisted()
    {
        // @todo: Review this, as it's not quite correct, key "name" should be sent to the front end, even if not set, to match V2 functionality
        $model = Author::make();

        $rules = [
            'model.name' => '',
        ];

        $expected = [
            'name' => null,
        ];

        $component = Livewire::test(ModelsComponent::class, ['model' => $model, 'rules' => $rules]);

        $results = $component->snapshot['data']['model'][0];

        $this->assertEquals($expected['name'], $results['name']);
    }

    public function test_it_ignores_the_key_if_the_model_does_not_exist()
    {
        $this->expectNotToPerformAssertions();

        $model = Author::make();

        $model->id = 123;

        Livewire::test(ModelsComponent::class, ['model' => $model])
            ->call('$refresh');
    }
}

class PostComponent extends TestComponent
{
    public $post;

    public function mount()
    {
        $this->post = Post::first();
    }
}

class ModelsComponent extends TestComponent
{
    public $model;
    public $models;

    public $_rules = [
        'model.title' => '',
        'model.name' => '',
        'model.email' => '',
        'model.posts.*.title' => '',
        'model.posts.*.description' => '',
        'model.posts.*.content' => '',
        'model.posts.*.comments.*.comment' => '',
        'model.posts.*.comments.*.author.name' => '',
        'model.posts.*.otherComments.*.comment' => '',
        'model.posts.*.otherComments.*.author.name' => '',
    ];

    protected function rules()
    {
        return $this->_rules;
    }

    public function mount($rules = null)
    {
        if (isset($rules)) {
            $this->_rules = $rules;
        }
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

    public function otherComments()
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

    public function otherComments()
    {
        return $this->hasMany(OtherComment::class);
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

class OtherComment extends Model
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
