@extends($view, $params)

@section($slotOrSection)
    {!! $manager->initialDehydrate()->toInitialResponse()->effects['html'] !!}
@endsection
