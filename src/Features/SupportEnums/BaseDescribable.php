<?php

namespace Livewire\Features\SupportEnums;

use Livewire\Enums\Description;

trait BaseDescribable
{
    public function description()
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Description::class);

        if (count($classAttributes) === 0) {
            throw new MissingDescriptionException($this->name);
        }

        return $classAttributes[0]->newInstance()->description;
    }
}
