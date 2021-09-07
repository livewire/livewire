<?php

namespace Livewire\Features;

use DateTime;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Livewire\Livewire;
use Illuminate\Support\Carbon as IlluminateCarbon;

class SupportDateTimes
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('property.dehydrate', function ($name, $value, $component, $response) {
            if (! $value instanceof \DateTime) return;

            $component->{$name} = $value->format(\DateTimeInterface::ISO8601);

            data_fill($response->memo, 'dataMeta.dates', []);

            if ($value instanceof IlluminateCarbon) {
                $response->memo['dataMeta']['dates'][$name] = 'illuminate';
            } elseif ($value instanceof Carbon) {
                $response->memo['dataMeta']['dates'][$name] = 'carbon';
            } elseif ($value instanceof CarbonImmutable) {
                $response->memo['dataMeta']['dates'][$name] = 'carbonImmutable';
            } elseif ($value instanceof DateTimeImmutable) {
                $response->memo['dataMeta']['dates'][$name] = 'nativeImmutable';
            } else {
                $response->memo['dataMeta']['dates'][$name] = 'native';
            }
        });

        Livewire::listen('property.hydrate', function ($name, $value, $component, $request) {
            $dates = data_get($request->memo, 'dataMeta.dates', []);

            $types = [
                'native' => DateTime::class,
                'nativeImmutable' => DateTimeImmutable::class,
                'carbon' => Carbon::class,
                'carbonImmutable' => CarbonImmutable::class,
                'illuminate' => IlluminateCarbon::class,
            ];

            foreach ($dates as $name => $type) {
                data_set($component, $name, new $types[$type]($value));
            }
        });
    }
}
