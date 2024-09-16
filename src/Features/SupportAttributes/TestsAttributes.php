<?php

namespace Livewire\Features\SupportAttributes;

use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use PHPUnit\Framework\Assert as PHPUnit;

trait TestsAttributes
{

    public function assertComponentHasAttribute(string $attribute, string|array|null $value = null): self
    {
        $attributes = $this->getAttributes();

        PHPUnit::assertTrue(
            $attributes->contains(fn($item) => $item instanceof $attribute),
            "Attribute {$attribute} does not exist on or within the component."
        );

        $this->checkValue(
            $value,
            $attributes->filter(fn($item) => $item instanceof $attribute),
            $attribute
        );

        return $this;
    }

    public function assertClassHasAttribute(string $attribute, string|array|null $value = null): self
    {
        $attributes = $this->getAttributes();

        $rootAttributes = $attributes->filter(fn($item) => $item->getLevel() === AttributeLevel::ROOT);

        PHPUnit::assertTrue(
            $rootAttributes->contains(fn($item) => $item instanceof $attribute),
            "Attribute {$attribute} does not exist on the class root level of the component."
        );

        $this->checkValue($value, $rootAttributes, $attribute);

        return $this;
    }

    public function assertMethodHasAttribute(string $methodName, string $attribute, string|array|null $value = null): self
    {
        $attributes = $this->getAttributes();

        if (!method_exists($this->instance(), $methodName)) {
            PHPUnit::fail("Method {$methodName} does not exist on the component.");
        }

        $methodAttributes = $attributes->filter(fn($item) => $item->getLevel() === AttributeLevel::METHOD && $item->getSubName() === $methodName
        );

        PHPUnit::assertTrue(
            $methodAttributes->contains(fn($item) => $item instanceof $attribute),
            "Attribute {$attribute} does not exist on or within the method."
        );

        $this->checkValue($value, $methodAttributes, $attribute);

        return $this;
    }

    public function assertPropertyHasAttribute(string $propertyName, string $attribute, string|array|null $value = null): self
    {
        $attributes = $this->getAttributes();

        if (!property_exists($this->instance(), $propertyName)) {
            PHPUnit::fail("Property {$propertyName} does not exist on the component.");
        }

        $propertyAttributes = $attributes->filter(fn($item) => $item->getLevel() === AttributeLevel::PROPERTY && $item->getSubName() === $propertyName
        );

        PHPUnit::assertTrue(
            $propertyAttributes->contains(fn($item) => $item instanceof $attribute),
            "Attribute {$attribute} does not exist on or within the property."
        );

        $this->checkValue($value, $propertyAttributes, $attribute);

        return $this;
    }

    private function getAttributes(): AttributeCollection
    {
        $attributes = $this->invade()->getAttributes();

        if ($attributes->isEmpty()) {
            PHPUnit::fail("No attributes exist on or within the component.");
        }

        return $attributes;
    }

    private function checkValue($value, $items, $attribute): void
    {
        if ($value !== null) {
            $result = false;

            if ($items instanceof Collection) {
                foreach ($items as $item) {
                    if ($this->attributeMatchesValue($value, $item, $attribute)) {
                        $result = true;
                        break;
                    }
                }
            } else {
                $result = $this->attributeMatchesValue($value, $items, $attribute);
            }

            PHPUnit::assertTrue($result, "Attribute {$attribute} does not contain the expected value.");
        }
    }

    private function attributeMatchesValue($value, $item, $attribute): bool
    {
        if (!$item instanceof $attribute) {
            return false;
        }

        $attributeComparisons = [
            On::class => ['event'],
            Validate::class => ['rule', 'as', 'message'],
            Title::class => ['content'],
            Session::class => ['key'],
            Lazy::class => ['isolate'],
            Computed::class => ['persist', 'cache', 'seconds', 'key', 'tags'],
        ];

        if (!isset($attributeComparisons[$attribute])) {
            PHPUnit::fail("Attribute {$attribute} is not supported for comparison.");
        }

        $fields = $attributeComparisons[$attribute];

        if (is_array($value)) {
            if (array_is_list($value)) {
                $assigned = false;
                foreach ($fields as $field) {
                    $actualValue = $this->getAttributeValue($item, $field);
                    if (is_array($actualValue)) {
                        $value = [$field => $value];
                        $assigned = true;
                        break;
                    }
                }
                if (!$assigned) {
                    return false;
                }
            }
        } else if (count($fields) === 1) {
            $value = [$fields[0] => $value];
        } else {
            return false;
        }

        foreach ($value as $key => $expectedValue) {
            if (in_array($key, $fields, true)) {
                $actualValue = $this->getAttributeValue($item, $key);

                if (is_array($expectedValue) && is_array($actualValue)) {
                     sort($expectedValue);
                     sort($actualValue);

                    if ($expectedValue !== $actualValue) {
                        return false;
                    }
                } else if ($actualValue !== $expectedValue) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    private function getAttributeValue($item, $key)
    {
        $methodName = 'get' . ucfirst($key);
        if (method_exists($item, $methodName)) {
            return $item->$methodName();
        }

        if (method_exists($item, $key)) {
            return $item->$key();
        }

        if (property_exists($item, $key)) {
            return $item->$key;
        }

        PHPUnit::fail("Unable to access field '{$key}' on attribute " . get_class($item) . ".");
    }

}
