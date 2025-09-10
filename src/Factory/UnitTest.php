<?php

namespace Livewire\Factory;

use Livewire\Factory\Fixtures\SimpleComponent;
use Illuminate\Support\Facades\File;
use Livewire\Compiler\CacheManager;
use Livewire\Compiler\Compiler;
use Livewire\Finder\Finder;
use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    protected $tempPath;
    protected $cacheDir;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempPath = sys_get_temp_dir() . '/livewire_compiler_test_' . uniqid();
        $this->cacheDir = $this->tempPath . '/cache';

        File::makeDirectory($this->tempPath, 0755, true);
        File::makeDirectory($this->cacheDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempPath)) {
            File::deleteDirectory($this->tempPath);
        }

        parent::tearDown();
    }

    public function test_can_create_simple_class_based_component()
    {
        $finder = new Finder();
        $compiler = new Compiler(new CacheManager($this->cacheDir));
        $factory = new Factory($finder, $compiler);

        $finder->addLocation(classNamespace: 'Livewire\Factory\Fixtures');

        $component = $factory->create('simple-component');

        $this->assertInstanceOf(SimpleComponent::class, $component);
        $this->assertEquals('simple-component', $component->getName());
        $this->assertNotNull($component->getId());
    }

    public function test_can_create_simple_class_based_component_with_custom_id()
    {
        $finder = new Finder();
        $compiler = new Compiler(new CacheManager($this->cacheDir));
        $factory = new Factory($finder, $compiler);

        $finder->addLocation(classNamespace: 'Livewire\Factory\Fixtures');

        $customId = 'custom-component-id';
        $component = $factory->create('simple-component', $customId);

        $this->assertInstanceOf(SimpleComponent::class, $component);
        $this->assertEquals('simple-component', $component->getName());
        $this->assertEquals($customId, $component->getId());
    }

    public function test_can_create_component_from_class_name()
    {
        $finder = new Finder();
        $compiler = new Compiler(new CacheManager($this->cacheDir));
        $factory = new Factory($finder, $compiler);

        $finder->addComponent(SimpleComponent::class);

        $component = $factory->create(SimpleComponent::class);

        $this->assertInstanceOf(SimpleComponent::class, $component);
    }

    public function test_can_create_component_from_class_instance()
    {
        $finder = new Finder();
        $compiler = new Compiler(new CacheManager($this->cacheDir));
        $factory = new Factory($finder, $compiler);

        $finder->addComponent(SimpleComponent::class);

        $existingComponent = new SimpleComponent();
        $component = $factory->create($existingComponent);

        $this->assertInstanceOf(SimpleComponent::class, $component);
        $this->assertNotSame($existingComponent, $component); // Should be a new instance
    }

    public function test_can_create_single_file_component()
    {
        $finder = new Finder();
        $compiler = new Compiler(new CacheManager($this->cacheDir));
        $factory = new Factory($finder, $compiler);

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $component = $factory->create('simple-single-file-component');

        $this->assertInstanceOf(Component::class, $component);
        $this->assertEquals('simple-single-file-component', $component->getName());
        $this->assertNotNull($component->getId());
    }

    public function test_can_create_multi_file_component()
    {
        $finder = new Finder();
        $compiler = new Compiler(new CacheManager($this->cacheDir));
        $factory = new Factory($finder, $compiler);

        $finder->addLocation(viewPath: __DIR__ . '/Fixtures');

        $component = $factory->create('simple-multi-file-component');

        $this->assertInstanceOf(Component::class, $component);
        $this->assertEquals('simple-multi-file-component', $component->getName());
        $this->assertNotNull($component->getId());
    }
}
