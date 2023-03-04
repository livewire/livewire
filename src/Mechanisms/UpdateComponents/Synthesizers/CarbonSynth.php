<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

use DateTime;
use Carbon\Carbon;

class CarbonSynth extends Synth {
    public static $types = [
        'native' => \DateTime::class,
        'nativeImmutable' => \DateTimeImmutable::class,
        'carbon' => \Carbon\Carbon::class,
        'carbonImmutable' => \Carbon\CarbonImmutable::class,
        'illuminate' => \Illuminate\Support\Carbon::class,
    ];

    public static $key = 'cbn';

    static function match($target) {
        foreach (static::$types as $type => $class) {
            if ($target instanceof $class) return true;
        }

        return false;
    }

    function dehydrate($target) {
        return [
            $target->format(\DateTimeInterface::ISO8601),
            ['type' => array_search(get_class($target), static::$types)],
        ];
    }

    function hydrate($value, $meta) {
        return new static::$types[$meta['type']]($value);
    }
}
