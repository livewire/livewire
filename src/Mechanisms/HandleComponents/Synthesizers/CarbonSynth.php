<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

use Carbon\CarbonImmutable;
use DateTime;
use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeInterface;

class CarbonSynth extends Synth {
    public static $types = [
        'native' => DateTime::class,
        'nativeImmutable' => DateTimeImmutable::class,
        'carbon' => Carbon::class,
        'carbonImmutable' => CarbonImmutable::class,
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
            $target->format(DateTimeInterface::ATOM),
            ['type' => array_search(get_class($target), static::$types)],
        ];
    }

    function hydrate($value, $meta) {
        return new static::$types[$meta['type']]($value);
    }
}
