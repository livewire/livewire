<?php

namespace Livewire;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

class RequestPayload implements Arrayable, Jsonable
{
    public $id;
    public $data;
    public $name;
    public $children;
    public $checksum;
    public $actionQueue;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->data = $data['data'];
        $this->name = $data['name'];
        $this->children = $data['children'];
        $this->checksum = $data['checksum'];
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
            'actionQueue' => $this->actionQueue,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
