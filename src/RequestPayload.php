<?php

namespace Livewire;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class RequestPayload implements Arrayable, Jsonable
{
    public $id;
    public $data;
    public $name;
    public $children;
    public $checksum;
    public $middleware;
    public $actionQueue;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->data = $data['data'];
        $this->name = $data['name'];
        $this->children = $data['children'];
        $this->checksum = $data['checksum'];
        $this->middleware = $data['middleware'];
        $this->actionQueue = $data['actionQueue'];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'data' => $this->data,
            'name' => $this->name,
            'children' => $this->children,
            'checksum' => $this->checksum,
            'middleware' => $this->middleware,
            'actionQueue' => $this->actionQueue,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
