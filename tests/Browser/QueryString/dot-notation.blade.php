<div>

    <input wire:model="filters.title" type="text" dusk="title">

    @foreach ($posts as $post)
        <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
    @endforeach

    <button wire:click="changePostTitle" dusk="changePostTitle">Change Title</button>
    <p>
        {{ json_encode($filters)  }}
    </p>
    {{ $posts->links() }}
</div>
