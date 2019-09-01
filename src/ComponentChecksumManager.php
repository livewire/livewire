<?php

namespace Livewire;

class ComponentChecksumManager
{
    public function generate($name, $id, $data)
    {
        $hashKey = app('encrypter')->getKey();

        $stringForHashing = $name.$id.json_encode($data);

        return hash_hmac('sha256', $stringForHashing, $hashKey);
    }

    public function check($checksum, $name, $id, $data)
    {
        return hash_equals($this->generate($name, $id, $data), $checksum);
    }
}
