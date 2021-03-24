<div>
    @foreach ($posts as $post)
        <h1 wire:key="post-{{ $post->id }}">{{ $post->title }}</h1>
    @endforeach

    {{ $posts->links('posts-list-pagination') }}

    @foreach ($messages as $message)
        <p wire:key="message-{{ $message->id }}">{{ $message->body }}</p>
    @endforeach

    {{ $messages->links() }}
</div>
