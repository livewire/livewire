## Partials

Take a look at the following example of using @partials for a basic "load more" feature.

```php
@php

use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Models\Post;

new class extends Livewire\Component {
    use WithPagination;

    #[Computed]
    public function posts()
    {
        return Post::paginate(10);
    }

    public function loadMore()
    {
        $this->nextPage();
    }
}
@endphp

<div>
    @partial(mode: 'append')
        @foreach ($posts as $post)
            <div>
                <h1>{{ $post->title }}</h1>
                <p>{{ $post->content }}</p>
            </div>
        @endforeach
    @endpartial

    @if ($posts->hasMorePages())
        <button type="button" wire:click="loadMore">Load More</button>
    @endif
</div>
```
