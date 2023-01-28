<?php

namespace Tests\Browser\DataBinding\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
                ->assertValue('@author.name', 'Bob Doe')
                ->assertValue('@author.email', 'bob@bob.com')
                ->assertValue('@author.posts.0.title', 'Post 1')
                ->assertValue('@author.posts.0.comments.0.comment', 'Comment 1')
                ->assertValue('@author.posts.0.comments.0.author.name', 'Bob Doe')
                ->assertValue('@author.posts.0.comments.1.comment', 'Comment 2')
                ->assertValue('@author.posts.0.comments.1.author.name', 'John Doe')
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
        $author->name = 'Bob Doe';
        $author->posts[0]->title = 'Post 1';
        $author->posts[0]->comments[0]->comment = 'Comment 1';
        $author->posts[0]->comments[1]->author->name = 'John Doe';
        $author->push();
    }

    /** @test */
    public function it_supports_multiword_relations_in_nested_data()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->assertValue('@author.posts.0.otherComments.0.comment', 'Other Comment 1')
                ->assertValue('@author.posts.0.otherComments.0.author.name', 'Bob Doe')
                ->assertValue('@author.posts.0.otherComments.1.comment', 'Other Comment 2')
                ->assertValue('@author.posts.0.otherComments.1.author.name', 'John Doe')

                ->waitForLivewire()->type('@author.posts.0.otherComments.0.comment', 'Other Message 1')
                ->assertSeeIn('@output.author.posts.0.otherComments.0.comment', 'Other Message 1')

                ->waitForLivewire()->type('@author.posts.0.otherComments.1.author.name', 'Randall')
                ->assertSeeIn('@output.author.posts.0.otherComments.1.author.name', 'Randall')

                ->waitForLivewire()->click('@save')
                ;
        });

        $author = Author::with(['posts', 'posts.comments', 'posts.comments.author', 'posts.otherComments', 'posts.otherComments.author'])->first();

        $this->assertEquals('Other Message 1', $author->posts[0]->otherComments[0]->comment);
        $this->assertEquals('Randall', $author->posts[0]->otherComments[1]->author->name);

        // Reset back after finished.
        $author->posts[0]->otherComments[0]->comment = 'Other Comment 1';
        $author->posts[0]->otherComments[1]->author->name = 'John Doe';
        $author->push();
    }

    /** @test */
    public function it_supports_attribute_casts()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire()->type('@author.name', 'Steve Doe')
                ->assertSeeIn('@output.author.name', 'Steve Doe')

                ->waitForLivewire()->click('@save')
            ;
        });

        $author = Author::first();

        $this->assertEquals('Steve', $author->first_name);
        $this->assertEquals('Doe', $author->last_name);

        // Reset back after finished.
        $author->name = 'John Doe';
        $author->push();
    }
}

if(class_exists(Attribute::class)) {
    class Author extends Model
    {
        use Sushi;

        protected $guarded = [];

        protected $rows = [
            ['id' => 1, 'name' => 'Bob Doe', 'email' => 'bob@bob.com'],
            ['id' => 2, 'name' => 'John Doe', 'email' => 'john@john.com']
        ];

        protected $appends = [
            'first_name',
            'last_name'
        ];

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
            return $this->hasMany(OtherComment::class);
        }

        public function firstName()
        {
            return Attribute::make(
                function() {
                    return Str::before($this->name, ' ');
                }
            );
        }

        public function lastName()
        {
            return Attribute::make(
                function() {
                    return Str::after($this->name, ' ');
                }
            );
        }
    }
} else {
    class Author extends Model
    {
        use Sushi;

        protected $guarded = [];

        protected $rows = [
            ['id' => 1, 'name' => 'Bob Doe', 'email' => 'bob@bob.com'],
            ['id' => 2, 'name' => 'John Doe', 'email' => 'john@john.com']
        ];

        protected $appends = [
            'first_name',
            'last_name'
        ];

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
            return $this->hasMany(OtherComment::class);
        }

        public function getFirstNameAttribute()
        {
            return Str::before($this->name, ' ');
        }

        public function getLastNameAttribute()
        {
            return Str::after($this->name, ' ');
        }
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

    public function otherComments()
    {
        return $this->hasMany(OtherComment::class);
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

class OtherComment extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'comment' => 'Other Comment 1', 'post_id' => 1, 'author_id' => 1],
        ['id' => 2, 'comment' => 'Other Comment 2', 'post_id' => 1, 'author_id' => 2]
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
