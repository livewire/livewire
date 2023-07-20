<?php

namespace Livewire\Features\SupportTesting;

use function Livewire\on;

abstract class Render
{
    protected function extractComponentAndBladeView($callback)
    {
        $instance = null;
        $extractedView = null;

        $offA = on('dehydrate', function ($component) use (&$instance) {
            $instance = $component;
        });

        $offB = on('render', function ($component, $view) use (&$extractedView) {
            return function () use ($view, &$extractedView) {
                $extractedView = $view;
            };
        });

        $result = $callback();

        $offA(); $offB();

        return [$result, $instance, $extractedView];
    }
}
