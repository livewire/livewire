<?php

namespace Livewire\Performance;

use Livewire\Component;
use Tests\TestCase;

class RenderPerformanceBenchmarkTest extends TestCase
{
    protected int $iterations = 1000;
    protected array $results = [];

    public function test_benchmark_simple_component_render()
    {
        $this->benchmark('Simple Component Render', function () {
            app('livewire')->mount(SimpleBenchmarkComponent::class);
        });
    }

    public function test_benchmark_component_with_properties()
    {
        $this->benchmark('Component with Properties', function () {
            app('livewire')->mount(ComponentWithProperties::class, ['name' => 'John', 'count' => 42]);
        });
    }

    public function test_benchmark_component_with_computed_property()
    {
        $this->benchmark('Component with Computed Property', function () {
            app('livewire')->mount(ComponentWithComputed::class);
        });
    }

    public function test_benchmark_component_with_template()
    {
        $this->benchmark('Component with Blade Template', function () {
            app('livewire')->mount(ComponentWithTemplate::class);
        });
    }

    public function test_benchmark_component_with_lifecycle_hooks()
    {
        $this->benchmark('Component with Lifecycle Hooks', function () {
            app('livewire')->mount(ComponentWithHooks::class);
        });
    }

    protected function benchmark(string $name, callable $callback)
    {
        // Warm up
        for ($i = 0; $i < 10; $i++) {
            $callback();
        }

        // Run benchmark
        $start = microtime(true);
        $memoryStart = memory_get_usage();

        for ($i = 0; $i < $this->iterations; $i++) {
            $callback();
        }

        $elapsed = microtime(true) - $start;
        $memoryUsed = memory_get_usage() - $memoryStart;

        // Calculate metrics
        $avgTime = ($elapsed / $this->iterations) * 1000; // Convert to ms
        $rendersPerSecond = $this->iterations / $elapsed;
        $avgMemory = $memoryUsed / $this->iterations;

        // Store results
        $this->results[$name] = [
            'total_time' => $elapsed,
            'avg_time_ms' => $avgTime,
            'renders_per_second' => $rendersPerSecond,
            'memory_used' => $memoryUsed,
            'avg_memory_bytes' => $avgMemory,
        ];

        // Output results
        echo "\n";
        echo "================================================\n";
        echo "Benchmark: {$name}\n";
        echo "================================================\n";
        echo "Iterations:        " . number_format($this->iterations) . "\n";
        echo "Total Time:        " . number_format($elapsed, 4) . "s\n";
        echo "Avg Time:          " . number_format($avgTime, 4) . "ms\n";
        echo "Renders/Second:    " . number_format($rendersPerSecond, 2) . "\n";
        echo "Memory Used:       " . $this->formatBytes($memoryUsed) . "\n";
        echo "Avg Memory/Render: " . $this->formatBytes($avgMemory) . "\n";
        echo "================================================\n";

        // Assert reasonable performance (adjust these thresholds as needed)
        $this->assertLessThan(
            10.0,
            $elapsed,
            "{$name}: {$this->iterations} renders took {$elapsed}s (expected < 10s)"
        );

        return $this->results[$name];
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function tearDown(): void
    {
        if (!empty($this->results)) {
            echo "\n";
            echo "================================================\n";
            echo "BENCHMARK SUMMARY\n";
            echo "================================================\n";

            foreach ($this->results as $name => $metrics) {
                echo sprintf(
                    "%-35s %8.2fms  %8.0f r/s\n",
                    $name . ':',
                    $metrics['avg_time_ms'],
                    $metrics['renders_per_second']
                );
            }

            echo "================================================\n";
        }

        parent::tearDown();
    }
}

// Benchmark Components

class SimpleBenchmarkComponent extends Component
{
    public function render()
    {
        return '<div>Simple Component</div>';
    }
}

class ComponentWithProperties extends Component
{
    public $name = '';
    public $count = 0;

    public function mount($name, $count)
    {
        $this->name = $name;
        $this->count = $count;
    }

    public function render()
    {
        return <<<'blade'
            <div>
                <span>Name: {{ $name }}</span>
                <span>Count: {{ $count }}</span>
            </div>
        blade;
    }
}

class ComponentWithComputed extends Component
{
    public $items = [];

    public function mount()
    {
        $this->items = range(1, 10);
    }

    #[\Livewire\Attributes\Computed]
    public function total()
    {
        return array_sum($this->items);
    }

    public function render()
    {
        return <<<'blade'
            <div>
                <span>Total: {{ $this->total }}</span>
            </div>
        blade;
    }
}

class ComponentWithTemplate extends Component
{
    public $title = 'Benchmark Title';
    public $description = 'This is a benchmark description with some text.';
    public $items = [];

    public function mount()
    {
        $this->items = ['Item 1', 'Item 2', 'Item 3'];
    }

    public function render()
    {
        return <<<'blade'
            <div>
                <h1>{{ $title }}</h1>
                <p>{{ $description }}</p>
                <ul>
                    @foreach($items as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        blade;
    }
}

class ComponentWithHooks extends Component
{
    public $data = [];

    public function mount()
    {
        $this->data = ['key' => 'value'];
    }

    public function boot()
    {
        // Lifecycle hook
    }

    public function booted()
    {
        // Lifecycle hook
    }

    public function hydrate()
    {
        // Lifecycle hook
    }

    public function render()
    {
        return '<div>{{ json_encode($data) }}</div>';
    }
}
