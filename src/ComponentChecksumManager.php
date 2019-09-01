<?php

namespace Livewire;

class ComponentChecksumManager
{
    public function generate($name, $id, $data)
    {
        $stringForHashing = $name.$id.json_encode($data);

        return password_hash($stringForHashing, PASSWORD_BCRYPT, [
            'cost' => 5,
        ]);
    }

    public function check($checksum, $name, $id, $data)
    {
        $sourceString = $name.$id.json_encode($data);

        return password_verify($sourceString, $checksum);
    }
}
