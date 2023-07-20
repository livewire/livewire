<div>
    @if (isset($errors) && $errors->has('bar')) sharedError:{{ $errors->first('bar') }} @endif
</div>
