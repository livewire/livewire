<?php

namespace Livewire\Attributes\Form;

use Livewire\Features\SupportFormObjects\Reset as BaseDontReset;

#[\Attribute]
class Reset extends BaseDontReset
{
    public function __construct(
        public $reset = true
    ) {
        //
    }
}
