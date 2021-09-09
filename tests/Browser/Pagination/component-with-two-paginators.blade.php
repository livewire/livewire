<div>
    <div>
        <div>
            @foreach ($posts as $post)
                <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
            @endforeach
        </div>

        {{ $posts->links() }}
    </div>

    <span dusk="page-pagination-hook">{{ $pageHookOutput }}</span>

    <div>
        <div>
            @foreach ($items as $item)
                    <h1 wire:key="item-{{ $item->id }}">{{ $item->title }}</h1>
            @endforeach
        </div>

        {{ $items->links() }}
    </div>

    <span dusk="item-page-pagination-hook">{{ $itemPageHookOutput }}</span>
</div>