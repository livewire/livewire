<div>
    <div>
        <div dusk="first-links">{{ $posts->links(null, ['paginationKey' => 1]) }}</div>

        @foreach ($posts as $post)
            <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
        @endforeach

        <div dusk="second-links">{{ $posts->links(null, ['paginationKey' => 2]) }}</div>
    </div>
</div>