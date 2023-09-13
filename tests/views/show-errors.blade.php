<div>
    <h1>Errors test</h1>

    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
</div>
