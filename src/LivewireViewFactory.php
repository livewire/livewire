<?php

namespace Livewire;

use Livewire\Component;
use Illuminate\View\Factory;

class LivewireViewFactory extends Factory
{
    protected $componentPushes = [];

    protected $componentPrepends = [];

    public function yieldPushContent($section, $default = '')
    {
        $output = parent::yieldPushContent($section, $default);

        $components = array_unique([
            ...array_keys($this->componentPrepends),
            ...array_keys($this->componentPushes),
        ]);

        $output .= "<div wire:ignore wire:stack=\"$section\">";
        foreach ($components as $id) {
            $output .= $this->yieldComponentPushContent($section, $id);
        }
        $output .= '</div>';

        return $output;
    }

    protected function yieldComponentPushContent($section, $id)
    {
        $output = "<div wire:stack-id=\"$id\">";

        if (isset($this->componentPrepends[$id][$section])) {
            $output .= implode(array_reverse($this->componentPrepends[$id][$section]));
        }

        if (isset($this->componentPushes[$id][$section])) {
            $output .= implode($this->componentPushes[$id][$section]);
        }

        $output .= '</div>';

        return $output;
    }

    protected function extendPush($section, $content)
    {
        if (!$id = data_get($this->shared('_instance'), 'id')) {
            return parent::extendPush($section, $content);
        }

        if (!isset($this->componentPushes[$id][$section])) {
            $this->componentPushes[$id][$section] = [];
        }

        if (!isset($this->componentPushes[$id][$section][$this->renderCount])) {
            $this->componentPushes[$id][$section][$this->renderCount] = $content;
        }
        else {
            $this->componentPushes[$id][$section][$this->renderCount] .= $content;
        }
    }

    protected function extendPrepend($section, $content)
    {
        if (!$id = data_get($this->shared('_instance'), 'id')) {
            return parent::extendPush($section, $content);
        }

        if (!isset($this->componentPrepends[$id][$section])) {
            $this->componentPrepends[$id][$section] = [];
        }

        if (!isset($this->componentPrepends[$id][$section][$this->renderCount])) {
            $this->componentPrepends[$id][$section][$this->renderCount] = $content;
        }
        else {
            $this->componentPrepends[$id][$section][$this->renderCount] = $content .
            $this->componentPrepends[$id][$section][$this->renderCount];
        }
    }

    public function flushStacks()
    {
        $this->componentPushes = [];
        $this->componentPrepends = [];

        parent::flushStacks();
    }

    public function getComponentStack(Component $component)
    {
        $sections = array_unique([
            ...array_keys($this->componentPrepends[$component->id] ?? []),
            ...array_keys($this->componentPushes[$component->id] ?? []),
        ]);

        return collect($sections)
            ->mapWithKeys(function ($section) use ($component) {
                return [
                    $section => $this->yieldComponentPushContent($section, $component->id),
                ];
            })
            ->all();
    }
}
