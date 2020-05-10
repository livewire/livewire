<?php

namespace Livewire;

use Illuminate\Http\Request;
use Livewire\Livewire;
use Livewire\Macros\PretendClassMethodIsControllerMethod;

class LivewireController
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function __call($component, $parameters)
    {
        $componentClass = app('livewire')->getComponentClass($component);
        $reflected = new \ReflectionClass($componentClass);

        $paramters = $reflected->hasMethod('mount')
                ? (new PretendClassMethodIsControllerMethod($reflected->getMethod('mount'), $this))->retrieveBindings()
                : [];

        $lastRenderedLivewireView = null;
        Livewire::listen('view:render', function ($view) use (&$lastRenderedLivewireView) {
            $lastRenderedLivewireView = $view;
        });

        $response = Livewire::mount($component, $paramters);

        $componentViewData = array_diff_key(
            $lastRenderedLivewireView->getData(),
            array_flip(['_instance', 'errors'])
        );

        return app('view')->file(__DIR__.'/Macros/livewire-shared-view.blade.php', [
            'layout' => $this->request->route()->getAction('layout') ?? 'layouts.app',
            'section' => $this->request->route()->getAction('section') ?? 'content',
            'livewireRenderedContent' => $response->dom,
        ])
        ->with($this->request->route()->layoutParamsFromLivewire ?? [])
        ->with(Livewire::componentViewDataIsShared() ? $componentViewData : []);
    }
}
