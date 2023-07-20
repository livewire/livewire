<div>
    <input wire:model.live="search" type="text" dusk="search">

    @foreach ($posts as $post)
        <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
    @endforeach

    {{ $posts->links() }}
</div>
