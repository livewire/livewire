<?php

namespace Tests\Browser\DataBinding\EloquentCollections;

use Illuminate\Database\Eloquent\Model;
use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Sushi\Sushi;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function it_displays_all_nested_data()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->assertValue('@authors.0.name', 'Bob')
                ->assertValue('@authors.0.email', 'bob@bob.com')
                ->assertValue('@authors.0.posts.0.title', 'Post 1')
                ->assertValue('@authors.0.posts.0.comments.0.comment', 'Comment 1')
                ->assertValue('@authors.0.posts.0.comments.0.author.name', 'Bob')
                ->assertValue('@authors.0.posts.0.comments.1.comment', 'Comment 2')
                ->assertValue('@authors.0.posts.0.comments.1.author.name', 'John')
                ->assertValue('@authors.0.posts.1.title', 'Post 2')
                ->assertValue('@authors.1.name', 'John')
                ->assertValue('@authors.1.email', 'john@john.com')
                ;
        });
    }

    /** @test */
    public function it_allows_nested_data_to_be_changed()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->type('@authors.0.name', 'Steve')
                ->assertSeeIn('@output.authors.0.name', 'Steve')

                ->waitForLivewire()->type('@authors.0.posts.0.title', 'Article 1')
                ->assertSeeIn('@output.authors.0.posts.0.title', 'Article 1')

                ->waitForLivewire()->type('@authors.0.posts.0.comments.0.comment', 'Message 1')
                ->assertSeeIn('@output.authors.0.posts.0.comments.0.comment', 'Message 1')

                ->waitForLivewire()->type('@authors.0.posts.0.comments.1.author.name', 'Mike')
                ->assertSeeIn('@output.authors.0.posts.0.comments.1.author.name', 'Mike')

                ->waitForLivewire()->click('@save')

                ->waitForLivewire()->type('@authors.1.name', 'Taylor')
                ->assertSeeIn('@output.authors.1.name', 'Taylor')
                ;
        });

        $author = Author::with(['posts', 'posts.comments', 'posts.comments.author'])->first();

        $this->assertEquals('Steve', $author->name);
        $this->assertEquals('Article 1', $author->posts[0]->title);
        $this->assertEquals('Message 1', $author->posts[0]->comments[0]->comment);
        $this->assertEquals('Mike', $author->posts[0]->comments[1]->author->name);

        // Reset back after finished.
        $author->name = 'Bob';
        $author->posts[0]->title = 'Post 1';
        $author->posts[0]->comments[0]->comment = 'Comment 1';
        $author->posts[0]->comments[1]->author->name = 'John';
        $author->push();

    }
}

class Author extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'name' => 'Bob', 'email' => 'bob@bob.com'],
        ['id' => 2, 'name' => 'John', 'email' => 'john@john.com']
    ];

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
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'author_id' => 1],
        ['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'author_id' => 1]
    ];

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
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'comment' => 'Comment 1', 'post_id' => 1, 'author_id' => 1],
        ['id' => 2, 'comment' => 'Comment 2', 'post_id' => 1, 'author_id' => 2]
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
