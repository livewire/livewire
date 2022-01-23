<?php

namespace Livewire\Types;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Livewire\LivewirePropertyType;
use Livewire\ReflectionPropertyType;
use ReflectionProperty;

class DateTimeType implements LivewirePropertyType
{
    public static string $default = DateTime::class;

    public static array $map = [
        'native' => DateTime::class,
        'nativeImmutable' => DateTimeImmutable::class,
        'carbon' => Carbon::class,
        'carbonImmutable' => CarbonImmutable::class,
        'illuminate' => IlluminateCarbon::class,
    ];

    public function hydrate($instance, $request, $name, $value)
    {
        if (! $type = $this->determineDateType($instance, $request, $name, $value)) {
            return new static::$default($value);
        }

        return new $type($value);
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        if (! $type = $this->determineDateType($instance, null, $name, $value)) {
            return $value;
        }

        $mapped = array_flip(static::$map)[$type] ?? null;

        if ($response && $mapped) $response->memo['dataMeta']['dates'][$name] = $mapped;

        return $value instanceof DateTimeInterface
            ? $value->format(DateTimeInterface::ISO8601)
            : $value;
    }

    protected function determineDateType($instance, $request, $name, $value)
    {
        if ($type = ReflectionPropertyType::get($instance, $name)) {
            return $type->getName();
        }

        if ($request) {
            $type = data_get($request, "memo.dataMeta.dates.$name");

            return static::$map[$type] ?? null;
        }

        return $value instanceof DateTimeInterface
            ? get_class($value) : null;
    }
}
