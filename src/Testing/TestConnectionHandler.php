<?php

namespace Livewire\Testing;

use Livewire\Connection\ConnectionHandler;

class TestConnectionHandler extends ConnectionHandler
{
    public $lastValidator;

    public function interceptValidator($validator)
    {
        $this->lastValidator = $validator;
    }
}
