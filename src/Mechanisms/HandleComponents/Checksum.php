<?php

namespace Livewire\Mechanisms\HandleComponents;

use function Livewire\trigger;

class Checksum {
    static function verify($snapshot) {
        $checksum = $snapshot['checksum'];

        unset($snapshot['checksum']);

        trigger('checksum.verify', $checksum, $snapshot);

        if ($checksum !== $comparitor = self::generate($snapshot)) {
            trigger('checksum.fail', $checksum, $comparitor, $snapshot);

            throw new CorruptComponentPayloadException;
        }
    }

    static function generate($snapshot) {
        $hashKey = app('encrypter')->getKey();

        $checksum = hash_hmac('sha256', json_encode($snapshot), $hashKey);

        trigger('checksum.generate', $checksum, $snapshot);

        return $checksum;
    }
}
