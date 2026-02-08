<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Component;
use Livewire\Livewire;
use Livewire\Exceptions\MaxNestingDepthExceededException;
use Tests\TestCase;

class NestingDepthUnitTest extends TestCase
{
    public function test_rejects_property_paths_exceeding_max_depth()
    {
        config()->set('livewire.payload.max_nesting_depth', 10);

        $this->expectException(MaxNestingDepthExceededException::class);
        $this->expectExceptionMessage('max_nesting_depth');

        Livewire::test(NestingDepthComponent::class)
            ->set('data' . str_repeat('.a', 15), 'value'); // 16 levels
    }

    public function test_allows_property_paths_within_max_depth()
    {
        config()->set('livewire.payload.max_nesting_depth', 10);

        $component = Livewire::test(NestingDepthComponent::class)
            ->set('data.level1.level2.level3', 'nested value'); // 4 levels

        $this->assertEquals(
            'nested value',
            $component->get('data.level1.level2.level3')
        );
    }

    public function test_allows_exactly_max_depth()
    {
        config()->set('livewire.payload.max_nesting_depth', 5);

        $component = Livewire::test(NestingDepthComponent::class)
            ->set('data.a.b.c.d', 'value'); // Exactly 5 levels

        $this->assertEquals('value', $component->get('data.a.b.c.d'));
    }

    public function test_rejects_one_over_max_depth()
    {
        config()->set('livewire.payload.max_nesting_depth', 5);

        $this->expectException(MaxNestingDepthExceededException::class);
        $this->expectExceptionMessage('max_nesting_depth');

        Livewire::test(NestingDepthComponent::class)
            ->set('data.a.b.c.d.e', 'value'); // 6 levels
    }

    public function test_depth_limit_can_be_disabled()
    {
        config()->set('livewire.payload.max_nesting_depth', null);

        // Should not throw even with very deep path
        $component = Livewire::test(NestingDepthComponent::class)
            ->set('data' . str_repeat('.x', 20), 'deep value'); // 21 levels

        // Just verify it didn't throw
        $this->assertTrue(true);
    }

    public function test_depth_limit_can_be_customized()
    {
        config()->set('livewire.payload.max_nesting_depth', 3);

        $this->expectException(MaxNestingDepthExceededException::class);
        $this->expectExceptionMessage('max_nesting_depth');

        Livewire::test(NestingDepthComponent::class)
            ->set('data.a.b.c', 'value'); // 4 levels, exceeds 3
    }

    public function test_single_level_property_always_works()
    {
        config()->set('livewire.payload.max_nesting_depth', 1);

        $component = Livewire::test(NestingDepthComponent::class)
            ->set('data', ['foo' => 'bar']); // 1 level

        $this->assertEquals(['foo' => 'bar'], $component->get('data'));
    }

    public function test_deep_nesting_attack_is_blocked_quickly()
    {
        config()->set('livewire.payload.max_nesting_depth', 10);

        $start = microtime(true);
        $exceptions = 0;

        $component = Livewire::test(NestingDepthComponent::class);

        // Attempt the attack from the vulnerability report
        $prefix = 'data' . str_repeat('.a', 100); // 101 levels deep

        for ($i = 0; $i < 100; $i++) {
            try {
                $component->set($prefix . $i, 1);
            } catch (\Exception $e) {
                $exceptions++;
            }
        }

        $elapsed = microtime(true) - $start;

        // All should have been rejected
        $this->assertEquals(100, $exceptions);

        // Should fail fast (< 1 second), not hang for 20 seconds
        $this->assertLessThan(1, $elapsed, "Attack took {$elapsed} seconds - should be blocked instantly");
    }
}

class NestingDepthComponent extends Component
{
    public $data = [];

    public function render()
    {
        return '<div></div>';
    }
}
