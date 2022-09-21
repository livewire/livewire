<?php

namespace Synthetic\Synthesizers;

use DateTime;
use Carbon\Carbon;

class CarbonSynth extends Synth {
    public $types = [
        'native' => \DateTime::class,
        'nativeImmutable' => \DateTimeImmutable::class,
        'carbon' => \Illuminate\Support\Carbon::class,
        'carbonImmutable' => \Carbon\CarbonImmutable::class,
        'illuminate' => \Carbon\Carbon::class,
    ];

    public static $key = 'cbn';

    static function match($target) {
        return $target instanceof Carbon;
    }

    function dehydrate($target, $context) {
        $context->addMeta('type', array_search(get_class($target), $this->types));

        $format = \DateTimeInterface::ISO8601;

        if (isset($context->annotationsFromParent()['format']) && isset($context->annotationsFromParent['format'][0])) {
            $format = $context->annotationsFromParent['format'][0];
            $context->addMeta('format', $format);
        }

        // return ['year' => '2012', 'month' => '08', 'day' => '23'];

        return $target->format($format);
    }

    function hydrate($value, $meta) {
        $format = $meta['format'] ?? \DateTimeInterface::ISO8601;

        $date = DateTime::createFromFormat($format, $value);

        return new $this->types[$meta['type']]($date);
    }
}
