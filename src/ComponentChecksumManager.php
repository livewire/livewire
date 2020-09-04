<?php

namespace Livewire;

class ComponentChecksumManager
{
    protected static $funky = 1;

    public function generate($fingerprint, $memo)
    {
        $hashKey = app('encrypter')->getKey();

        $stringForHashing = ''
            .json_encode($fingerprint)
            .json_encode($memo);

        $suffix = static::$funky++;
        header("X-Fingerprint-$suffix: ".json_encode($fingerprint));
        header("X-Memo-$suffix: ".json_encode($memo));
        header("X-String-$suffix: ".$stringForHashing);

        $checksum =  hash_hmac('sha256', $stringForHashing, $hashKey);

        header("X-Checksum-$suffix: ".$checksum);
        return $checksum;
    }

    public function check($checksum, $fingerprint, $memo)
    {
        if (! hash_equals($this->generate($fingerprint, $memo), $checksum)) {
            // dump($fingerprint);
            // dump($memo);

            // dd($this->generate($fingerprint, $memo));
        }

        return hash_equals($this->generate($fingerprint, $memo), $checksum);
    }
}
