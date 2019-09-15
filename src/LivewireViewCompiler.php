<?php

namespace Livewire;

class LivewireViewCompiler
{
    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function __invoke()
    {
        $compiler = app('blade.compiler');
        $exposedCompiler = new ObjectPrybar($compiler);

        // Grab the "customDirectives" property from inside the compiler.
        // It's normally "protected" so we have to pry it open.
        // We'll add what we need for Livewire, then put
        // it back to the way we found it.
        $customDirectives = $tmp = $exposedCompiler->getProperty('customDirectives');

        if (! isset($customDirectives['this'])) {
            $customDirectives['this'] = [LivewireBladeDirectives::class, 'this'];
        }

        $exposedCompiler->setProperty('customDirectives', $customDirectives);

        $result = $this->view->render();

        $exposedCompiler->setProperty('customDirectives', $tmp);

        return $result;
    }
}
