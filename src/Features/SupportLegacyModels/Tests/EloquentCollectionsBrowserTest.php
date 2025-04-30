<?php

namespace Livewire\Features\SupportLegacyModels\Tests;

use Illuminate\Database\Eloquent\Model;
use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;
use Livewire\Component as BaseComponent;
use Sushi\Sushi;

class EloquentCollectionsBrowserTest extends TestCase
{
    use Concerns\EnableLegacyModels;

    public function test_it_displays_all_nested_data()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, EloquentCollectionsComponent::class)
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

    public function test_it_allows_nested_data_to_be_changed()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, EloquentCollectionsComponent::class)
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

        $author = EloquentCollectionsAuthor::with(['posts', 'posts.comments', 'posts.comments.author'])->first();

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

    public function test_hydrate_works_properly_without_rules()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, EloquentCollectionsWithoutRulesComponent::class)
                ->waitForLivewire()->click('@something')
                ->assertSeeIn('@output', 'Ok!');
            ;
        });
    }

    public function test_hydrate_works_properly_when_collection_is_empty()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, EloquentCollectionsWithoutItemsComponent::class)
                ->waitForLivewire()->click('@something')
                ->assertSeeIn('@output', 'Ok!');
            ;
        });
    }
}

class EloquentCollectionsComponent extends BaseComponent
{
    public $authors;

    protected $rules = [
        'authors.*.name' => '',
        'authors.*.email' => '',
        'authors.*.posts.*.title' => '',
        'authors.*.posts.*.comments.*.comment' => '',
        'authors.*.posts.*.comments.*.author.name' => '',
    ];

    public function mount()
    {
        $this->authors = EloquentCollectionsAuthor::with(['posts', 'posts.comments', 'posts.comments.author'])->get();
    }

    public function save()
    {
        $this->authors->each->push();
    }

    public function render()
    {
        return
        <<<'HTML'
<div>
    <button type="button" wire:click="$refresh">Refresh</button>
    @foreach($authors as $authorKey => $author)
        <div>
            <div>
                Author Name
                <input dusk='authors.{{ $authorKey }}.name' wire:model.live='authors.{{ $authorKey }}.name' />
                <span dusk='output.authors.{{ $authorKey }}.name'>{{ $author->name }}</span>

                Author Email
                <input dusk='authors.{{ $authorKey }}.email' wire:model.live='authors.{{ $authorKey }}.email' />
                <span dusk='output.authors.{{ $authorKey }}.email'>{{ $author->email }}</span>
            </div>

            <div>
                @foreach($author->posts as $postKey => $post)
                    <div>
                        Post Title
                        <input dusk='authors.{{ $authorKey }}.posts.{{ $postKey }}.title' wire:model.live="authors.{{ $authorKey }}.posts.{{ $postKey }}.title" />
                        <span dusk='output.authors.{{ $authorKey }}.posts.{{ $postKey }}.title'>{{ $post->title }}</span>

                        <div>
                            @foreach($post->comments as $commentKey => $comment)
                                <div>
                                    Comment Comment
                                    <input
                                        dusk='authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'
                                        wire:model.live="authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment"
                                        />
                                    <span dusk='output.authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.comment'>{{ $comment->comment }}</span>

                                    Commment Author Name
                                    <input
                                        dusk='authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'
                                        wire:model.live="authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name"
                                        />
                                    <span dusk='output.authors.{{ $authorKey }}.posts.{{ $postKey }}.comments.{{ $commentKey }}.author.name'>{{ optional($comment->author)->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <button wire:click="save" type="button" dusk="save">Save</button>
</div>
HTML;
    }
}

class EloquentCollectionsWithoutRulesComponent extends EloquentCollectionsComponent
{
    public $output;

    protected $rules = [];

    public function something()
    {
        $this->output = 'Ok!';
    }

    public function render()
    {
        return
        <<<'HTML'
<div>
      <div>
          @foreach($authors as $author)
              <p>{{ $author->name }}</p>
          @endforeach
      </div>
      <span dusk='output'>{{ $output }}</span>
      <button dusk='something' wire:click='something'>something</button>
</div>
HTML;

    }
}

class EloquentCollectionsWithoutItemsComponent extends BaseComponent
{
    public $authors;

    public $output;

    protected $rules = [];

    public function mount()
    {
        $this->authors = EloquentCollectionsWithoutItems::get();
    }

    public function something()
    {
        $this->output = 'Ok!';
    }

    public function render()
    {
        return
            <<<'HTML'
<div>
      <div>
          @foreach($authors as $author)
              <p>{{ $author->name }}</p>
          @endforeach
      </div>
      <span dusk='output'>{{ $output }}</span>
      <button dusk='something' wire:click='something'>something</button>
</div>
HTML;

    }
}

class EloquentCollectionsAuthor extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'name' => 'Bob', 'email' => 'bob@bob.com'],
        ['id' => 2, 'name' => 'John', 'email' => 'john@john.com'],
    ];

    public function posts()
    {
        return $this->hasMany(EloquentCollectionsPost::class);
    }

    public function comments()
    {
        return $this->hasMany(EloquentCollectionsComment::class);
    }
}

class EloquentCollectionsPost extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'title' => 'Post 1', 'description' => 'Post 1 Description', 'content' => 'Post 1 Content', 'eloquent_collections_author_id' => 1],
        ['id' => 2, 'title' => 'Post 2', 'description' => 'Post 2 Description', 'content' => 'Post 2 Content', 'eloquent_collections_author_id' => 1],
    ];

    public function author()
    {
        return $this->belongsTo(EloquentCollectionsAuthor::class, 'eloquent_collections_author_id');
    }

    public function comments()
    {
        return $this->hasMany(EloquentCollectionsComment::class);
    }
}

class EloquentCollectionsComment extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [
        ['id' => 1, 'comment' => 'Comment 1', 'eloquent_collections_post_id' => 1, 'eloquent_collections_author_id' => 1],
        ['id' => 2, 'comment' => 'Comment 2', 'eloquent_collections_post_id' => 1, 'eloquent_collections_author_id' => 2],
    ];

    public function author()
    {
        return $this->belongsTo(EloquentCollectionsAuthor::class, 'eloquent_collections_author_id');
    }

    public function post()
    {
        return $this->belongsTo(EloquentCollectionsPost::class, 'eloquent_collections_post_id');
    }
}


class EloquentCollectionsWithoutItems extends Model
{
    use Sushi;

    protected $guarded = [];

    protected $rows = [];
}
