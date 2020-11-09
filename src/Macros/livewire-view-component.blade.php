@component($view, $params)
    {!! $manager->initialDehydrate()->toInitialResponse()->effects['html']; !!}
@endcomponent
