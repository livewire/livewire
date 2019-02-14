<?php

namespace Livewire;

class LivewireRouteDefinition
{
    protected $layout = null;
    protected $section = null;

    public function layout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    public function section($section)
    {
        $this->section = $section;

        return $this;
    }

    public function get($uri, $component, $options = [])
    {
        $this->layout = $options['layout'] ?? $this->layout;
        $this->section = $options['section'] ?? $this->section;

        return app('router')->get($uri, function() use ($component) {
            $route = app('router')->current();

            if ($this->layout) {
                $layout = $route->getAction('layout') ?? '' . $this->layout;
            } else {
                $layout = $route->getAction('layout') ?? 'layouts.app';
            }

            $section = $this->section ?: $route->getAction('section') ?? 'content';

            return app('view')->file(__DIR__ . '/livewire-view.blade.php', [
                'layout' => $layout,
                'section' => $section,
                'component' => $component,
            ]);
        });
    }
}
