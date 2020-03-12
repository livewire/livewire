<div>
    {{ session()->has('errors') && session()->get('errors')->has('bar') ? 'sessionInsideNewSyntaxComponentError:'.session()->get('errors')->first('bar') : '' }}
</div>
