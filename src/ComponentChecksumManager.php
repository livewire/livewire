<?php

namespace Livewire;

class ComponentChecksumManager
{
    public function generate($name, $id, $data)
    {
        return md5($name.$id.json_encode($data));
    }

    public function check($checksum, $name, $id, $data)
    {
        return $checksum === $this->generate($name, $id, $data);
    }
}
