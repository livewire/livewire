<?php

/**
 * Memory Leak Benchmark Script
 *
 * This script simulates what happens in Laravel Octane where the application
 * stays in memory across multiple requests. It measures memory growth over
 * simulated requests to detect memory leaks.
 *
 * Usage: php scripts/memory-benchmark.php [--no-flush] [--requests=1000]
 *
 * --no-flush: Disable flush-state calls (simulates the old broken behavior)
 * --requests: Number of requests to simulate (default: 1000)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use Livewire\Features\SupportRedirects\SupportRedirects;
use Livewire\Features\SupportStreaming\SupportStreaming;
use Livewire\Drawer\BaseUtils;

// Parse arguments
$noFlush = in_array('--no-flush', $argv);
$requests = 1000;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--requests=')) {
        $requests = (int) substr($arg, 11);
    }
}

echo "Memory Leak Benchmark\n";
echo "=====================\n";
echo "Requests to simulate: {$requests}\n";
echo "Flush state after each request: " . ($noFlush ? "NO (old behavior)" : "YES (new behavior)") . "\n\n";

// Sample component classes to simulate variety (50 different component types)
$componentClasses = [];
for ($i = 0; $i < 50; $i++) {
    $className = "BenchmarkComponent{$i}";
    eval("class {$className} { public \$data = []; }");
    $componentClasses[] = $className;
}

// Reflection setup for accessing protected statics
$lifecycleReflection = new ReflectionClass(SupportLifecycleHooks::class);
$traitCacheProp = $lifecycleReflection->getProperty('traitCache');
$traitCacheProp->setAccessible(true);
$methodCacheProp = $lifecycleReflection->getProperty('methodCache');
$methodCacheProp->setAccessible(true);

$baseUtilsReflection = new ReflectionClass(BaseUtils::class);
$reflectionCacheProp = $baseUtilsReflection->getProperty('reflectionCache');
$reflectionCacheProp->setAccessible(true);

$streamingReflection = new ReflectionClass(SupportStreaming::class);
$responseProp = $streamingReflection->getProperty('response');
$responseProp->setAccessible(true);

// Flush function that simulates calling Livewire::flushState()
function flushState() {
    global $traitCacheProp, $methodCacheProp, $reflectionCacheProp, $responseProp;

    // Clear HandleComponents stacks
    HandleComponents::$componentStack = [];
    HandleComponents::$renderStack = [];

    // Clear SupportLifecycleHooks caches
    $traitCacheProp->setValue(null, []);
    $methodCacheProp->setValue(null, []);

    // Clear BaseUtils reflection cache
    $reflectionCacheProp->setValue(null, []);

    // Clear SupportRedirects stack
    SupportRedirects::$redirectorCacheStack = [];

    // Clear SupportStreaming response
    $responseProp->setValue(null, null);
}

// Ensure clean start
flushState();
gc_collect_cycles();

$startMemory = memory_get_usage(true);
$peakMemory = $startMemory;

echo "Starting memory: " . formatBytes($startMemory) . "\n\n";

// Simulate requests
for ($request = 1; $request <= $requests; $request++) {
    // Simulate component lifecycle
    $componentClass = $componentClasses[array_rand($componentClasses)];
    $component = new $componentClass();
    $component->data = array_fill(0, 10, str_repeat('x', 100)); // ~1KB data

    // === Simulate what happens during mount/update ===

    // 1. Component gets pushed onto stack
    HandleComponents::$componentStack[] = $component;
    HandleComponents::$renderStack[] = $component;

    // 2. Lifecycle hooks cache the component's traits and methods
    $traitCache = $traitCacheProp->getValue(null);
    $traitCache[$componentClass] = ['Trait1', 'Trait2', 'Trait3'];
    $traitCacheProp->setValue(null, $traitCache);

    $methodCache = $methodCacheProp->getValue(null);
    $methodCache[$componentClass . '::mount'] = true;
    $methodCache[$componentClass . '::hydrate'] = true;
    $methodCache[$componentClass . '::render'] = true;
    $methodCache[$componentClass . '::dehydrate'] = true;
    $methodCacheProp->setValue(null, $methodCache);

    // 3. BaseUtils caches reflection data
    $reflectionCache = $reflectionCacheProp->getValue(null);
    $reflectionCache[$componentClass] = [
        'data' => ['name' => 'data', 'type' => 'array'],
        'id' => ['name' => 'id', 'type' => 'string'],
        'name' => ['name' => 'name', 'type' => 'string'],
    ];
    $reflectionCacheProp->setValue(null, $reflectionCache);

    // 4. Simulate occasional "exception" leaving redirector stack imbalanced
    if ($request % 10 === 0) {
        SupportRedirects::$redirectorCacheStack[] = new stdClass();
    }

    // 5. Simulate streaming response being set
    if ($request % 50 === 0) {
        $responseProp->setValue(null, new stdClass());
    }

    // === End of request ===

    // In OLD behavior: nothing clears the static state
    // In NEW behavior: flush-state event clears everything
    if (!$noFlush) {
        flushState();
    }

    // Track peak memory
    $currentMemory = memory_get_usage(true);
    if ($currentMemory > $peakMemory) {
        $peakMemory = $currentMemory;
    }

    // Report every 100 requests
    if ($request % 100 === 0) {
        echo "Request {$request}: " . formatBytes($currentMemory) .
             " (+" . formatBytes($currentMemory - $startMemory) . ")\n";
    }
}

$endMemory = memory_get_usage(true);

echo "\n";
echo "=== RESULTS ===\n";
echo "Final memory: " . formatBytes($endMemory) . "\n";
echo "Peak memory: " . formatBytes($peakMemory) . "\n";
echo "Memory growth: " . formatBytes($endMemory - $startMemory) . "\n";
echo "Growth per request: " . formatBytes(($endMemory - $startMemory) / $requests) . "\n";

// Report static state sizes
echo "\n";
echo "Static State Sizes (end of benchmark):\n";
echo "--------------------------------------\n";
echo "HandleComponents::\$componentStack: " . count(HandleComponents::$componentStack) . " items\n";
echo "HandleComponents::\$renderStack: " . count(HandleComponents::$renderStack) . " items\n";
echo "SupportRedirects::\$redirectorCacheStack: " . count(SupportRedirects::$redirectorCacheStack) . " items\n";
echo "SupportLifecycleHooks::\$traitCache: " . count($traitCacheProp->getValue(null)) . " entries\n";
echo "SupportLifecycleHooks::\$methodCache: " . count($methodCacheProp->getValue(null)) . " entries\n";
echo "BaseUtils::\$reflectionCache: " . count($reflectionCacheProp->getValue(null)) . " entries\n";
echo "SupportStreaming::\$response: " . ($responseProp->getValue(null) ? "SET" : "null") . "\n";

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
}
