<?php

declare(strict_types=1);

namespace Tests\PHPStan\Reflection;

use Livewire\PHPStan\Properties\ComputedPropertyExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\VerbosityLevel;
use Tests\PHPStan\stubs\TestComponentWithComputedProperties;

final class ComputedPropertyExtensionTest extends PHPStanTestCase
{
    private ClassReflection $classReflection;
    private ComputedPropertyExtension $reflectionExtension;

    public function setUp(): void
    {
        parent::setUp();

        $this->classReflection = $this->createReflectionProvider()
            ->getClass(TestComponentWithComputedProperties::class);

        $this->reflectionExtension = new ComputedPropertyExtension();
    }

    /** @test */
    public function itMustHaveComputedAttribute(): void
    {
        $this->assertFalse($this->reflectionExtension->hasProperty(
            $this->classReflection,
            'notAComputedProperty',
        ));
    }

    /** @test */
    public function itMustBePublicMethod(): void
    {
        $this->assertFalse($this->reflectionExtension->hasProperty(
            $this->classReflection,
            'privateMethod',
        ));

        $this->assertFalse($this->reflectionExtension->hasProperty(
            $this->classReflection,
            'protectedMethod',
        ));

        $this->assertTrue($this->reflectionExtension->hasProperty(
            $this->classReflection,
            'property',
        ));
    }

    /** @test */
    public function itReturnsDocComment(): void
    {
        $property = $this->reflectionExtension->getProperty(
            $this->classReflection,
            'propertyWithComments',
        );

        $this->assertSame(
            $property->getDocComment(),
            '/** This is a comment. */',
        );
    }

    /** @test */
    public function itRespectsDeprecatedDoc(): void
    {
        $property = $this->reflectionExtension->getProperty(
            $this->classReflection,
            'property',
        );
        $deprecatedProperty = $this->reflectionExtension->getProperty(
            $this->classReflection,
            'deprecatedProperty',
        );

        $this->assertTrue($property->isDeprecated()->no());
        $this->assertTrue($deprecatedProperty->isDeprecated()->yes());
    }

    /** @test */
    public function itReturnsDeprecatedDescription(): void
    {
        $property = $this->reflectionExtension->getProperty(
            $this->classReflection,
            'property',
        );
        $deprecatedProperty = $this->reflectionExtension->getProperty(
            $this->classReflection,
            'deprecatedPropertyWithDescription',
        );

        $this->assertNull($property->getDeprecatedDescription());
        $this->assertSame(
            $deprecatedProperty->getDeprecatedDescription(),
            'Has a description.'
        );
    }

    /** @test */
    public function itReturnsType(): void
    {
        $property = $this->reflectionExtension->getProperty(
            $this->classReflection,
            'property',
        );

        $this->assertSame(
            $property->getReadableType()->describe(VerbosityLevel::typeOnly()),
            'bool',
        );
    }

    /** @test */
    public function itReturnsGenericType(): void
    {
        $property = $this->reflectionExtension->getProperty(
            $this->classReflection,
            'propertyWithGenerics',
        );

        $this->assertSame(
            $property->getReadableType()->describe(VerbosityLevel::typeOnly()),
            'array<int, string>',
        );
    }
}
