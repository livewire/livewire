<?php

namespace Livewire;

class Ai extends BaseAi
{
    //


    public static function stringContainsDots($string) {
        return strpos($string, '.') !== false;
    } // __END__
}
