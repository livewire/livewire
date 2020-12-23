<?php

namespace Livewire;

class Request
{
    public $fingerprint;
    public $updates;
    public $memo;

    public function __construct($payload)
    {
        $this->fingerprint = $payload['fingerprint'];
        $this->updates = $payload['updates'];
        $this->memo = $payload['serverMemo'];
    }

    public function id() { return $this->fingerprint['id']; }

    public function name() { return $this->fingerprint['name']; }
}
