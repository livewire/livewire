<?php

namespace Livewire;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class ResponsePayload implements Arrayable, Jsonable
{
    public $id;
    public $dom;
    public $data;
    public $checksum;
    public $children;
    public $eventQueue;
    public $redirectTo;
    public $dirtyInputs;
    public $events;
    public $fromPrefetch;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->dom = $data['dom'];
        $this->data = $data['data'];
        $this->checksum = $data['checksum'];
        $this->children = $data['children'];
        $this->eventQueue = $data['eventQueue'];
        $this->redirectTo = $data['redirectTo'];
        $this->dirtyInputs = $data['dirtyInputs'];
        $this->events = $data['events'];
        $this->fromPrefetch = $data['fromPrefetch'];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'dom' => $this->dom,
            'data' => $this->data,
            'checksum' => $this->checksum,
            'children' => $this->children,
            'eventQueue' => $this->eventQueue,
            'redirectTo' => $this->redirectTo,
            'dirtyInputs' => $this->dirtyInputs,
            'events' => $this->events,
            'fromPrefetch' => $this->fromPrefetch,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
