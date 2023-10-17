<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class CarbonSynth extends Synth
{
    public static $types = [
        'native' => DateTime::class,
        'nativeImmutable' => DateTimeImmutable::class,
        'carbon' => Carbon::class,
        'carbonImmutable' => CarbonImmutable::class,
        'illuminate' => \Illuminate\Support\Carbon::class,
    ];

    public static $key = 'cbn';

    public static function match($target)
    {
        foreach (static::$types as $type => $class) {
            if ($target instanceof $class) {
                return true;
            }
        }

        return false;
    }

    public function dehydrate($target)
    {
        return [
            $target->format(DateTimeInterface::ATOM),
            ['type' => array_search(get_class($target), static::$types)],
        ];
    }

    public function hydrate($value, $meta)
    {
        return new static::$types[$meta['type']]($value);
    }
}
