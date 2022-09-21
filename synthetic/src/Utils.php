<?php

namespace Synthetic;

class Utils
{
    static function getPublicPropertiesDefinedOnSubclass($target) {
        return static::getPublicProperties($target, function ($property) use ($target) {
            // dump($property->getDeclaringClass()->getName(), $target::class, $property);
            // Filter out any properties from parents...
            return $property->getDeclaringClass()->getName() === $target::class;
        });
    }

    static function getPublicProperties($target, $filter)
    {
        return collect((new \ReflectionObject($target))->getProperties())
            ->filter(function ($property) {
                return $property->isPublic() && ! $property->isStatic() && $property->isDefault();
            })
            ->filter($filter ?? fn () => true)
            ->mapWithKeys(function ($property) use ($target) {
                // Ensures typed property is initialized in PHP >=7.4, if so, return its value,
                // if not initialized, return null (as expected in earlier PHP Versions)
                $value = method_exists($property, 'isInitialized') && !$property->isInitialized($target)
                    ? null
                    : $property->getValue($target);

                return [$property->getName() => $value];
            })
            ->all();
    }

    static function getPublicMethodsDefinedBySubClass($target)
    {
        $methods = array_filter((new \ReflectionObject($target))->getMethods(), function ($method) use ($target) {
            $isInSyntheticTrait = str($method->getFilename())->afterLast('/')->exactly('Synthetic.php');

            return $method->isPublic()
                && ! $method->isStatic()
                && ! $isInSyntheticTrait
                && $method->getDeclaringClass()->getName() === $target::class;
        });

        return array_map(function ($method) {
            return $method->getName();
        }, $methods);
    }

    static function propertyHasAnnotation($target, $property, $annotation) {
        foreach (static::getAnnotations($target) as $prop => $annotations) {
            if ($prop === $property && array_key_exists($annotation, $annotations)) {
                return true;
            }
        }

        return false;
    }

    static function getAnnotations($target) {
        if (! is_object($target)) return [];

        return collect()
            ->concat((new \ReflectionClass($target))->getProperties())
            ->concat((new \ReflectionClass($target))->getMethods())
            ->filter(function ($subject) use ($target) {
                if ($subject->class !== get_class($target)) return false;
                if ($subject->getDocComment() === false) return false;
                return true;
            })
            ->mapWithKeys(function ($subject) {
                return [$subject->getName() => static::parseAnnotations($subject->getDocComment())];
            })->toArray();
    }

    static function parseAnnotations($raw) {
        return str($raw)
            ->matchAll('/\@([^\*]+)/')
            ->mapWithKeys(function ($line) {
                $segments = explode(' ', trim($line));

                $annotation = array_shift($segments);

                return [$annotation => $segments];
            })
            ->toArray();
    }
}
