<?php

namespace Livewire;

class ComponentChecksumManager
{
    public function generate($fingerprint, $memo)
    {
        $hashKey = app('encrypter')->getKey();

        $stringForHashing = ''
            .json_encode($fingerprint)
            .json_encode($memo);

        return hash_hmac('sha256', $stringForHashing, $hashKey);
    }

    public function check($checksum, $fingerprint, $memo)
    {
        return hash_equals($this->generate($fingerprint, $memo), $checksum);
    }
}
