<?php

declare(strict_types=1);

namespace Livewire\PHPStan\Properties;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;

final class ComputedProperty implements PropertyReflection
{
    public function __construct(
        private ClassReflection $declaringClass,
        private ExtendedMethodReflection $methodReflection,
        private Type $readableType,
    ) {
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return $this->methodReflection->getDocComment();
    }

    public function getReadableType(): Type
    {
        return $this->readableType;
    }

    public function getWritableType(): Type
    {
        return new NeverType();
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return false;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->methodReflection->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->methodReflection->getDeprecatedDescription();
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
}
