<x-dynamic-component :component="$view">
    {!! $manager->initialDehydrate()->toInitialResponse()->effects['html']; !!}
</x-dynamic-component>
