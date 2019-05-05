<?php

namespace Livewire;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;


class LivewireOutput implements Arrayable, Jsonable
{
    public $id;
    public $dom;
    public $data;
    public $children;
    public $dirtyInputs;
    public $listeningFor;
    public $eventQueue;
    public $checksum;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->dom = $data['dom'];
        $this->data = $data['data'];
        $this->children = $data['children'];
        $this->dirtyInputs = $data['dirtyInputs'];
        $this->listeningFor = $data['listeningFor'];
        $this->eventQueue = $data['eventQueue'];
        $this->checksum = $data['checksum'];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'dom' => $this->dom,
            'data' => $this->data,
            'children' => $this->children,
            'dirtyInputs' => $this->dirtyInputs,
            'listeningFor' => $this->listeningFor,
            'eventQueue' => $this->eventQueue,
            'checksum' => $this->checksum,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
