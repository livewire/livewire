<?php

namespace Livewire\Features\SupportEnums;

use Livewire\Enums\Display;

#[\Attribute]
class BaseDisplay
{
    public string $as;

    public function __construct($as) {
        $this->as = $as;
    }

    public static function from($case)
    {
        $ref = new \ReflectionClassConstant($case::class, $case->name);

        $classAttributes = $ref->getAttributes(Display::class);

        if (count($classAttributes) === 0) {
            return false;
        }

        return $classAttributes[0]->newInstance()->as;
    }
}
