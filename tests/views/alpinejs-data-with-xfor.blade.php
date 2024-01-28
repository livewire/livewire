<div>
    <h1>Example Component</h1>
    <button wire:click.prevent="loadPosts" dusk="loadPosts">Load</button>
    <div x-data="{
            posts: @js($posts)
        }"
    >
        <template x-for="post in posts">
            <div x-text="post.title"></div>
        </template>
    </div>
</div>
