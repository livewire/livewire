<div>
    @foreach ($posts as $post)
        <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
    @endforeach

    {{ $posts->links() }}
</div>
