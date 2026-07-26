<?php

namespace Livewire\Features\SupportAttributes;

use Illuminate\Support\Collection;
use Livewire\Features\SupportFormObjects\Form;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionNamedType;

// A component's attribute collection is a fact about its CLASS SHAPE, not a
// runtime accumulation: the component's own attributes, plus — for every
// form-typed member (a declared `public PostForm $form` property or a
// #[Virtual] method with a Form return type) — that form class's attributes
// under the member's path prefix. Because the full tree is derivable from
// the class alone, it is complete before any lifecycle window runs and never
// changes when instances are built, replaced, or reset. Discovery reflects
// once per class per process; instantiation binds per component...
class AttributeCollection extends Collection
{
    protected static array $discovered = [];

    static function fromComponent($component)
    {
        $instance = new static;

        foreach (static::specs(get_class($component)) as [$attribute, $level, $name, $subName, $subTargetClass]) {
            $instance->push(tap($attribute->newInstance(), function ($attr) use ($component, $level, $name, $subName, $subTargetClass) {
                $attr->__boot($component, $level, $name, $subName, $subTargetClass);
            }));
        }

        return $instance;
    }

    protected static function specs($class)
    {
        return static::$discovered[$class] ??= static::discover($class);
    }

    protected static function discover($class)
    {
        $specs = static::discoverClassShape(new ReflectionClass($class), null, '');

        foreach (static::formMembers($class) as $name => $formClass) {
            $specs = [
                ...$specs,
                ...static::discoverClassShape(new ReflectionClass($formClass), $formClass, $name . '.'),
            ];
        }

        return $specs;
    }

    protected static function discoverClassShape(ReflectionClass $reflected, $subTargetClass, $prefix)
    {
        $specs = [];

        foreach (static::getClassAttributesRecursively($reflected) as $attribute) {
            // Form-level class attributes carry their member's path as a name
            // so consumers can tell which member they belong to...
            $rootName = $prefix === '' ? null : rtrim($prefix, '.');

            $specs[] = [$attribute, AttributeLevel::ROOT, $rootName, null, $subTargetClass];
        }

        foreach ($reflected->getMethods() as $method) {
            foreach ($method->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $specs[] = [$attribute, AttributeLevel::METHOD, $prefix . $method->getName(), $method->getName(), $subTargetClass];
            }
        }

        foreach ($reflected->getProperties() as $property) {
            foreach ($property->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $specs[] = [$attribute, AttributeLevel::PROPERTY, $prefix . $property->getName(), $property->getName(), $subTargetClass];
            }
        }

        return $specs;
    }

    // The form-typed members of a class: declared typed properties and
    // #[Virtual] methods whose (statically enforced, concrete) return type
    // is a Form. One level deep only — forms don't nest...
    protected static function formMembers($class)
    {
        $members = [];

        foreach ((new ReflectionClass($class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) continue;

            $type = $property->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) continue;

            if (is_a($type->getName(), Form::class, true)) {
                $members[$property->getName()] = $type->getName();
            }
        }

        if (method_exists($class, 'virtualPropertyTypes')) {
            foreach ($class::virtualPropertyTypes() as $name => $type) {
                if (is_a($type, Form::class, true)) {
                    $members[$name] = $type;
                }
            }
        }

        return $members;
    }

    protected static function getClassAttributesRecursively($reflected) {
        $attributes = [];

        while ($reflected) {
            foreach ($reflected->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $attributes[] = $attribute;
            }

            $reflected = $reflected->getParentClass();
        }

        return $attributes;
    }
}
