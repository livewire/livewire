<?php

namespace Livewire\ProtectedStorage;

use Livewire\Component;

interface ProtectedStorage
{
    public function getProtectedDataForPayload(Component $instance);

    public function saveProtectedData(Component $instance);

    public function restoreProtectedData(Component $unHydratedInstance, $payloadData);

}
