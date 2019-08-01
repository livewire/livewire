<?php

namespace Livewire;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class ResponsePayload implements Arrayable, Jsonable
{
    public $id;
    public $dom;
    public $data;
    public $children;
    public $eventQueue;
    public $redirectTo;
    public $dirtyInputs;
    public $events;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->dom = $data['dom'];
        $this->data = $data['data'];
        $this->children = $data['children'];
        $this->eventQueue = $data['eventQueue'];
        $this->redirectTo = $data['redirectTo'];
        $this->dirtyInputs = $data['dirtyInputs'];
        $this->events = $data['events'];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'dom' => $this->dom,
            'data' => $this->data,
            'children' => $this->children,
            'eventQueue' => $this->eventQueue,
            'redirectTo' => $this->redirectTo,
            'dirtyInputs' => $this->dirtyInputs,
            'events' => $this->events,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
