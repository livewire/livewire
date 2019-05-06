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
    public $dirtyInputs;
    public $listeningFor;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->dom = $data['dom'];
        $this->data = $data['data'];
        $this->children = $data['children'];
        $this->eventQueue = $data['eventQueue'];
        $this->dirtyInputs = $data['dirtyInputs'];
        $this->listeningFor = $data['listeningFor'];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'dom' => $this->dom,
            'data' => $this->data,
            'children' => $this->children,
            'eventQueue' => $this->eventQueue,
            'dirtyInputs' => $this->dirtyInputs,
            'listeningFor' => $this->listeningFor,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
