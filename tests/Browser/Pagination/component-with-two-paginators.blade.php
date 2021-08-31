<div>
    <div>
        @foreach ($posts as $post)
            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
        @endforeach

        {{ $posts->links() }}
    </div>

    <div>
        @foreach ($items as $item)
                <h1 wire:key="item-{{ $item->id }}">{{ $item->title }}</h1>
        @endforeach

        {{ $items->links() }}
    </div>
</div>