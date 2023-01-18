<?php

namespace Livewire\Features\SupportQueryString;

trait HandlesQueryString
{
    public function getQueryString()
    {
        $componentQueryString = [];

        $componentQueryString = method_exists($this, 'queryString')
            ? $this->queryString()
            : $this->queryString;

        return collect(class_uses_recursive($class = static::class))
            ->map(function ($trait) use ($class) {
                $member = 'queryString' . class_basename($trait);

                if (method_exists($class, $member)) {
                    return $this->{$member}();
                }

                if (property_exists($class, $member)) {
                    return $this->{$member};
                }

                return [];
            })
            ->values()
            ->mapWithKeys(function ($value) {
                return $value;
            })
            ->merge($componentQueryString)
            ->toArray();
    }
}
