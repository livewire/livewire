<?php

namespace Livewire\Drawer;

class PropertyTypeUnitTest extends \Tests\TestCase
{
    function test_it_matches_values_against_named_property_types()
    {
        $target = new PropertyTypeFixture;

        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'string', 'value'));
        $this->assertFalse(Utils::propertyTypeMatchesValue($target, 'string', 1));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'integer', 1));
        $this->assertFalse(Utils::propertyTypeMatchesValue($target, 'integer', '1'));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'float', 1.5));
        $this->assertFalse(Utils::propertyTypeMatchesValue($target, 'float', 1));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'boolean', true));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'array', []));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'object', new \stdClass));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'iterable', []));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'mixed', new \stdClass));
    }

    function test_it_matches_nullable_and_union_property_types()
    {
        $target = new PropertyTypeFixture;

        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'nullableString', null));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'nullableString', 'value'));
        $this->assertFalse(Utils::propertyTypeMatchesValue($target, 'nullableString', []));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'stringOrArray', 'value'));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'stringOrArray', []));
        $this->assertFalse(Utils::propertyTypeMatchesValue($target, 'stringOrArray', 1));
    }

    function test_it_matches_class_and_interface_property_types()
    {
        $target = new PropertyTypeFixture;

        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'objectType', new PropertyTypeObject));
        $this->assertFalse(Utils::propertyTypeMatchesValue($target, 'objectType', new \stdClass));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'interfaceType', new PropertyTypeObject));
        $this->assertTrue(Utils::propertyTypeMatchesValue($target, 'intersectionType', new PropertyTypeObject));
        $this->assertFalse(Utils::propertyTypeMatchesValue($target, 'intersectionType', new PropertyTypeInterfaceOnlyObject));
    }

    function test_it_reports_when_there_is_no_declared_type_to_match_against()
    {
        $target = new PropertyTypeFixture;

        $this->assertNull(Utils::propertyTypeMatchesValue($target, 'untyped', 'value'));
        $this->assertNull(Utils::propertyTypeMatchesValue($target, 'missing', 'value'));
    }
}

interface PropertyTypeInterface
{
}

class PropertyTypeObject implements PropertyTypeInterface, \Stringable
{
    public function __toString(): string
    {
        return '';
    }
}

class PropertyTypeInterfaceOnlyObject implements PropertyTypeInterface
{
}

class PropertyTypeFixture
{
    public string $string;
    public int $integer;
    public float $float;
    public bool $boolean;
    public array $array;
    public object $object;
    public iterable $iterable;
    public mixed $mixed;
    public ?string $nullableString;
    public string|array $stringOrArray;
    public PropertyTypeObject $objectType;
    public PropertyTypeInterface $interfaceType;
    public PropertyTypeInterface&\Stringable $intersectionType;
    public $untyped;
}
