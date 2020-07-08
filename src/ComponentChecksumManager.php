<?php

namespace Livewire;

class ComponentChecksumManager
{
    public function generate($name, $id, $data, $meta)
    {
        $hashKey = app('encrypter')->getKey();

        $stringForHashing = $name.$id.json_encode($data).json_encode($meta);

        return hash_hmac('sha256', $stringForHashing, $hashKey);
    }

    public function check($checksum, $name, $id, $data, $meta)
    {
        return hash_equals($this->generate($name, $id, $data, $meta), $checksum);
    }
}
