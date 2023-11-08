<?php

declare(strict_types=1);

namespace Livewire\PHPStan\Properties;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\PHPStan\Properties\ComputedProperty;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;

final class ComputedPropertyExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if (! $classReflection->isSubclassOf(Component::class)) {
            return false;
        }

        if ($classReflection->isAbstract()) {
            return false;
        }

        if (! $classReflection->hasNativeMethod($propertyName)) {
            return false;
        }

        $methodReflection = $classReflection
            ->getNativeReflection()
            ->getMethod($propertyName);

        if (! $methodReflection->isPublic()) {
            return false;
        }

        return ! empty($methodReflection->getAttributes(Computed::class));
    }

    public function getProperty(
        ClassReflection $classReflection,
        string $propertyName,
    ): PropertyReflection {
        $methodReflection = $classReflection->getNativeMethod($propertyName);

        $returnType = ParametersAcceptorSelector::selectSingle(
            $methodReflection->getVariants(),
        )->getReturnType();

        return new ComputedProperty(
            declaringClass: $classReflection,
            methodReflection: $methodReflection,
            readableType: $returnType,
        );
    }
}
