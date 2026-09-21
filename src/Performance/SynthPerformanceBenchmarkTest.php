<?php

namespace Livewire\Performance;

use Livewire\Component;
use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Tests\TestCase;
use Illuminate\Support\Stringable;

class SynthPerformanceBenchmarkTest extends TestCase
{
    protected int $iterations = 1000;
    protected array $results = [];

    public function test_benchmark_mount_with_collection_state()
    {
        $this->benchmark('Mount with Collection State', function () {
            app('livewire')->mount(SynthCollectionComponent::class);
        });
    }

    public function test_benchmark_mount_with_mixed_synth_state()
    {
        $this->benchmark('Mount with Mixed Synth State', function () {
            app('livewire')->mount(SynthMixedComponent::class);
        });
    }

    public function test_benchmark_update_with_mixed_synth_state()
    {
        $html = app('livewire')->mount(SynthMixedComponent::class);
        $snapshot = Utils::extractAttributeDataFromHtml($html, 'wire:snapshot');
        $handle = app(HandleComponents::class);

        $this->benchmark('Update with Mixed Synth State', function () use ($handle, $snapshot) {
            $handle->update($snapshot, ['count' => 99, 'label' => 'updated'], []);
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
        $opsPerSecond = $this->iterations / $elapsed;
        $avgMemory = $memoryUsed / $this->iterations;

        // Store results
        $this->results[$name] = [
            'total_time' => $elapsed,
            'avg_time_ms' => $avgTime,
            'ops_per_second' => $opsPerSecond,
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
        echo "Ops/Second:        " . number_format($opsPerSecond, 2) . "\n";
        echo "Memory Used:       " . $this->formatBytes($memoryUsed) . "\n";
        echo "Avg Memory/Op:     " . $this->formatBytes((int) $avgMemory) . "\n";
        echo "================================================\n";

        // Assert reasonable performance (adjust these thresholds as needed)
        $this->assertLessThan(
            30.0,
            $elapsed,
            "{$name}: {$this->iterations} ops took {$elapsed}s (expected < 30s)"
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
        if (! empty($this->results)) {
            echo "\n";
            echo "================================================\n";
            echo "BENCHMARK SUMMARY\n";
            echo "================================================\n";

            foreach ($this->results as $name => $metrics) {
                echo sprintf(
                    "%-40s %8.2fms  %8.0f ops/s\n",
                    $name . ':',
                    $metrics['avg_time_ms'],
                    $metrics['ops_per_second']
                );
            }

            echo "================================================\n";
        }

        parent::tearDown();
    }
}

class SynthCollectionComponent extends Component
{
    public $items;

    public function mount()
    {
        $this->items = collect(range(1, 50))->map(fn ($i) => [
            'id' => $i,
            'name' => "Item {$i}",
            'nested' => ['x' => $i, 'y' => $i * 2],
        ]);
    }

    public function render()
    {
        return '<div></div>';
    }
}

class SynthMixedComponent extends Component
{
    public int $count = 0;
    public string $label = 'bench';
    public array $tags = [];
    public $items;
    public $meta;
    public $title;
    public $created;

    public function mount()
    {
        $this->count = 10;
        $this->label = 'heavy';
        $this->tags = ['a', 'b', 'c'];
        $this->items = collect(range(1, 40))->map(fn ($i) => [
            'id' => $i,
            'name' => "Item {$i}",
            'nested' => ['x' => $i, 'y' => $i * 2],
        ]);
        $this->meta = (object) ['source' => 'bench', 'version' => 1];
        $this->title = new Stringable('A stringable title');
        $this->created = now();
    }

    public function render()
    {
        return '<div></div>';
    }
}
