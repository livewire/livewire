<?php

namespace Livewire;

use Livewire\Livewire;

class HttpConnectionHandler extends ConnectionHandler
{
    public function __invoke()
    {
        $serialized = request('serialized');
        $component = request('component');

        if ($serialized) {
            $livewire = decrypt($serialized);
        } else {
            $livewire = Livewire::activate($component, new \StdClass);
        }

        return $this->handle(request()->all(), $livewire) + [
            'serialized' => encrypt($livewire),
        ];
    }
}
