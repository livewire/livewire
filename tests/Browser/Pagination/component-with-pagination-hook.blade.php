<div>
    <div>

        @foreach ($posts as $post)
            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
        @endforeach

        {{ $posts->links(null, ['paginatorId' => 2]) }}
    </div>

    <span dusk="pagination-hook">{{ $hookOutput }}</span>
</div>