<?php

namespace Livewire;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;


class LivewireOutput implements Arrayable, Jsonable
{
    public $id;
    public $dom;
    public $data;
    public $dirtyInputs;
    public $listeningFor;
    public $eventQueue;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->dom = $data['dom'];
        $this->data = $data['data'];
        $this->dirtyInputs = $data['dirtyInputs'];
        $this->listeningFor = $data['listeningFor'];
        $this->eventQueue = $data['eventQueue'];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'dom' => $this->dom,
            'data' => $this->data,
            'dirtyInputs' => $this->dirtyInputs,
            'listeningFor' => $this->listeningFor,
            'eventQueue' => $this->eventQueue,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
