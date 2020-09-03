<div>
    <nav>
        @foreach (Tests\Browser\SyncHistory\User::all() as $nav_user)
            <button dusk="user-{{ $nav_user->id }}"
                wire:click="setUser({{ $nav_user->id }})"
                >
                {{ $nav_user->username }}
            </button>
        @endforeach

        <h1>Current: {{ $user->username }}</h1>

        <hr />

        <h2>{{ $liked ? 'liked' : 'not-liked' }}</h2>
        <button dusk="toggle-like" wire:click="toggleLike">Toggle Like</button>
</div>
