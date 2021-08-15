<?php

namespace Tests\Browser\DataBinding\Models;

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
                // ->tinker()
                ->assertValue('@author.name', 'Bob')
                ->assertValue('@author.email', 'bob@bob.com')
                ->assertValue('@author.posts.0.title', 'Post 1')
                ->assertValue('@author.posts.0.comments.0.comment', 'Comment 1')
                ->assertValue('@author.posts.0.comments.0.author.name', 'Bob')
                ->assertValue('@author.posts.0.comments.1.comment', 'Comment 2')
                ->assertValue('@author.posts.0.comments.1.author.name', 'John')
                ->assertValue('@author.posts.1.title', 'Post 2')
                ;
        });
    }

    /** @test */
    public function it_enables_nested_data_to_be_changed()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->type('@author.name', 'Steve')
                ->assertSeeIn('@output.author.name', 'Steve')

                ->waitForLivewire()->type('@author.posts.0.title', 'Article 1')
                ->assertSeeIn('@output.author.posts.0.title', 'Article 1')

                ->waitForLivewire()->type('@author.posts.0.comments.0.comment', 'Message 1')
                ->assertSeeIn('@output.author.posts.0.comments.0.comment', 'Message 1')

                ->waitForLivewire()->type('@author.posts.0.comments.1.author.name', 'Mike')
                ->assertSeeIn('@output.author.posts.0.comments.1.author.name', 'Mike')

                ->waitForLivewire()->click('@save')
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
