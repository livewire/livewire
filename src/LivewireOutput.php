<?php

namespace Livewire;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;


class LivewireOutput implements Arrayable, Jsonable
{
    public $id;
    public $dom;
    public $serialized;
    public $dirtyInputs;
    public $listeningFor;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->dom = $data['dom'];
        $this->serialized = $data['serialized'];
        $this->dirtyInputs = $data['dirtyInputs'];
        $this->listeningFor = $data['listeningFor'];
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'dom' => $this->dom,
            'serialized' => $this->serialized,
            'dirtyInputs' => $this->dirtyInputs,
            'listeningFor' => $this->listeningFor,
        ];
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
