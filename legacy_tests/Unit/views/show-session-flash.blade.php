<div>
    @if (session('status'))
        <div class="alert-success mb-" role="alert">
            {{ session('status') }}
        </div>
    @endif
</div>
