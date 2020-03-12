<div>
    {{ session()->has('errors') && session()->get('errors')->has('bar') ? 'sessionInsideAtSyntaxComponentError:'.session()->get('errors')->first('bar') : '' }}
</div>
