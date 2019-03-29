<?php

namespace Livewire\Concerns;

trait RegistersListeners
{
    protected $currentChild;
    protected $queuedListeners = [];

    public function setCurrentChildBeingRenderedInView($id)
    {
        $this->currentChild = $id;
    }

    public function prepareListenerForRegistration($event, $action)
    {
        $this->queuedListeners[$event] = $action;
    }

    public function registerListeners()
    {
        if ($this->queuedListeners) {
            $this->listenersByInternalChildComponentId[$this->currentChild]
                = $this->queuedListeners;
        }

        $this->queuedListeners = [];
        $this->currentChild = null;
    }

    public function listeners($componentId = null)
    {
        return $componentId
            ? $this->listenersByInternalChildComponentId[$componentId]
            : $this->listenersByInternalChildComponentId;
    }
}
