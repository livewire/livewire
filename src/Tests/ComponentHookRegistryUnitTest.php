<?php

namespace Livewire\Tests;

use Livewire\Component;
use Livewire\ComponentHook;
use Livewire\ComponentHookRegistry;
use Livewire\Livewire;

class ComponentHookRegistryUnitTest extends \Tests\TestCase
{
    public function test_pure_static_provide_hooks_are_not_instantiated_per_component()
    {
        $invocations = 0;
        CustomStaticProvideHook::$provided = false;
        CustomStaticProvideHook::$instantiations = 0;

        ComponentHookRegistry::register(CustomStaticProvideHook::class);

        $this->assertTrue(CustomStaticProvideHook::$provided);

        Livewire::test(HookRegistryTestComponent::class);

        $this->assertSame(0, CustomStaticProvideHook::$instantiations);
    }

    public function test_hooks_with_lifecycle_methods_are_instantiated_and_executed()
    {
        CustomLifecycleHook::$bootCalled = 0;
        CustomLifecycleHook::$mountCalled = 0;

        ComponentHookRegistry::register(CustomLifecycleHook::class);

        Livewire::test(HookRegistryTestComponent::class);

        $this->assertSame(1, CustomLifecycleHook::$bootCalled);
        $this->assertSame(1, CustomLifecycleHook::$mountCalled);
    }

    public function test_hooks_with_skip_are_skipped()
    {
        CustomSkippedHook::$bootCalled = 0;

        ComponentHookRegistry::register(CustomSkippedHook::class);

        Livewire::test(HookRegistryTestComponent::class);

        $this->assertSame(0, CustomSkippedHook::$bootCalled);
    }

    public function test_hooks_with_inherited_lifecycle_methods_are_active()
    {
        ChildInheritedLifecycleHook::$childBootCalled = 0;

        ComponentHookRegistry::register(ChildInheritedLifecycleHook::class);

        Livewire::test(HookRegistryTestComponent::class);

        $this->assertSame(1, ChildInheritedLifecycleHook::$childBootCalled);
    }

    public function test_hooks_with_custom_instance_methods_are_active()
    {
        ComponentHookRegistry::register(CustomMethodHook::class);

        $component = Livewire::test(HookRegistryTestComponent::class)->instance();

        $hook = ComponentHookRegistry::getHook($component, CustomMethodHook::class);

        $this->assertInstanceOf(CustomMethodHook::class, $hook);
        $this->assertSame('custom_result', $hook->customMethod());
    }

    public function test_get_hook_lazily_resolves_registered_static_hook_if_requested()
    {
        ComponentHookRegistry::register(LazyResolvedStaticHook::class);

        $component = Livewire::test(HookRegistryTestComponent::class)->instance();

        // Initially not in active hooks, but getHook should lazily instantiate it
        $hook = ComponentHookRegistry::getHook($component, LazyResolvedStaticHook::class);

        $this->assertInstanceOf(LazyResolvedStaticHook::class, $hook);
    }
}

class HookRegistryTestComponent extends Component
{
    public function render()
    {
        return '<div></div>';
    }
}

class CustomStaticProvideHook extends ComponentHook
{
    public static bool $provided = false;
    public static int $instantiations = 0;

    public static function provide()
    {
        static::$provided = true;
    }

    public function __construct()
    {
        static::$instantiations++;
    }
}

class CustomLifecycleHook extends ComponentHook
{
    public static int $bootCalled = 0;
    public static int $mountCalled = 0;

    public function boot()
    {
        static::$bootCalled++;
    }

    public function mount()
    {
        static::$mountCalled++;
    }
}

class CustomSkippedHook extends ComponentHook
{
    public static int $bootCalled = 0;

    public function skip()
    {
        return true;
    }

    public function boot()
    {
        static::$bootCalled++;
    }
}

abstract class BaseInheritedLifecycleHook extends ComponentHook
{
    public function boot()
    {
        static::$childBootCalled++;
    }
}

class ChildInheritedLifecycleHook extends BaseInheritedLifecycleHook
{
    public static int $childBootCalled = 0;
}

class CustomMethodHook extends ComponentHook
{
    public function customMethod(): string
    {
        return 'custom_result';
    }
}

class LazyResolvedStaticHook extends ComponentHook
{
    public static function provide() {}
}
