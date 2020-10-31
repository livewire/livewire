<?php
declare(strict_types=1);

namespace Livewire\Concerns;

trait EncodesJsonSafely
{
    protected static $javascriptMaxInteger = 2**53-1;

    protected static function stringEncodeTooLargeIntegers(&$mixed)
    {
        if (is_numeric($mixed) && $mixed > self::$javascriptMaxInteger) {
            $mixed = (string) $mixed;
        } elseif (is_array($mixed)) {
            array_walk_recursive($mixed, function (&$value, $key) {
                self::stringEncodeTooLargeIntegers($value);
            });
        }
    }
}
