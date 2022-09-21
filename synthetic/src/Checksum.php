<?php

namespace Synthetic;

use Exception;

class Checksum {
    static function verify($snapshot) {

        $checksum = $snapshot['checksum'];

        unset($snapshot['checksum']);

        app('synthetic')->trigger('checksum.verify', $checksum, $snapshot);

        if ($checksum !== $comparitor = self::generate($snapshot)) {
            app('synthetic')->trigger('checksum.fail', $checksum, $comparitor, $snapshot);

            throw new Exception;
        }
    }

    static function generate($snapshot) {
        $hashKey = app('encrypter')->getKey();

        $checksum = hash_hmac('sha256', json_encode($snapshot), $hashKey);

        app('synthetic')->trigger('checksum.generate', $checksum, $snapshot);

        return $checksum;
    }
}
