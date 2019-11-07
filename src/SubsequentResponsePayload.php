<?php

namespace Livewire;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class SubsequentResponsePayload extends ResponsePayload implements Arrayable, Jsonable
{
    public $id;
    public $name;
    public $dom;
    public $data;
    public $checksum;
    public $children;
    public $eventQueue;
    public $redirectTo;
    public $dirtyInputs;
    public $events;
    public $fromPrefetch;
    public $gc;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->dom = $data['dom'];
        $this->children = $data['children'];
        $this->eventQueue = $data['eventQueue'];
        $this->redirectTo = $data['redirectTo'];
        $this->dirtyInputs = $data['dirtyInputs'];
        $this->events = $data['events'];
        $this->fromPrefetch = $data['fromPrefetch'];
        $this->gc = $data['gc'];
    }

    public function setData($value)
    {
        $this->data = $value;
    }

    public function setChecksum($value)
    {
        $this->checksum = $value;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'dom' => $this->injectComponentDataAsHtmlAttributesInRootElement(
                $this->dom, ['id' => $this->id]
            ),
            'data' => $this->data,
            'checksum' => $this->checksum,
            'children' => $this->children,
            'eventQueue' => $this->eventQueue,
            'redirectTo' => $this->redirectTo,
            'dirtyInputs' => $this->dirtyInputs,
            'events' => $this->events,
            'fromPrefetch' => $this->fromPrefetch,
            'gc' => $this->gc,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
