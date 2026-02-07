<?php

/**
 * Realistic Memory Leak Benchmark
 *
 * Simulates real Livewire components with larger data (like Eloquent models)
 * to show actual memory growth in Octane.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Features\SupportLifecycleHooks\SupportLifecycleHooks;
use Livewire\Features\SupportRedirects\SupportRedirects;
use Livewire\Drawer\BaseUtils;

$noFlush = in_array('--no-flush', $argv);
$requests = 500;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--requests=')) {
        $requests = (int) substr($arg, 11);
    }
}

echo "Realistic Memory Leak Benchmark\n";
echo "================================\n";
echo "Requests: {$requests}\n";
echo "Flush: " . ($noFlush ? "NO" : "YES") . "\n\n";

// Create 100 unique component classes (simulating a real app)
$componentClasses = [];
for ($i = 0; $i < 100; $i++) {
    $className = "RealisticComponent{$i}";
    eval("class {$className} { public \$users = []; public \$posts = []; public \$settings = []; }");
    $componentClasses[] = $className;
}

// Reflection setup
$lifecycleReflection = new ReflectionClass(SupportLifecycleHooks::class);
$traitCacheProp = $lifecycleReflection->getProperty('traitCache');
$traitCacheProp->setAccessible(true);
$methodCacheProp = $lifecycleReflection->getProperty('methodCache');
$methodCacheProp->setAccessible(true);

$baseUtilsReflection = new ReflectionClass(BaseUtils::class);
$reflectionCacheProp = $baseUtilsReflection->getProperty('reflectionCache');
$reflectionCacheProp->setAccessible(true);

function flushAllState() {
    global $traitCacheProp, $methodCacheProp, $reflectionCacheProp;
    HandleComponents::$componentStack = [];
    HandleComponents::$renderStack = [];
    $traitCacheProp->setValue(null, []);
    $methodCacheProp->setValue(null, []);
    $reflectionCacheProp->setValue(null, []);
    SupportRedirects::$redirectorCacheStack = [];
}

// Clean start
flushAllState();
gc_collect_cycles();

$startMemory = memory_get_usage(true);
echo "Start: " . formatBytes($startMemory) . "\n\n";

for ($request = 1; $request <= $requests; $request++) {
    $componentClass = $componentClasses[array_rand($componentClasses)];
    $component = new $componentClass();

    // Simulate realistic component data (~50KB per component)
    // Like a table component with 100 user records
    $component->users = [];
    for ($j = 0; $j < 100; $j++) {
        $component->users[] = [
            'id' => $j,
            'name' => str_repeat('User Name ', 5),
            'email' => 'user' . $j . '@example.com',
            'bio' => str_repeat('Lorem ipsum dolor sit amet ', 10),
            'settings' => ['theme' => 'dark', 'notifications' => true],
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];
    }

    // Push to stacks (simulating component lifecycle)
    HandleComponents::$componentStack[] = $component;
    HandleComponents::$renderStack[] = $component;

    // Simulate cache growth
    $traitCache = $traitCacheProp->getValue(null);
    $traitCache[$componentClass] = ['WithPagination', 'WithSorting', 'WithFiltering'];
    $traitCacheProp->setValue(null, $traitCache);

    $methodCache = $methodCacheProp->getValue(null);
    for ($m = 0; $m < 20; $m++) {
        $methodCache[$componentClass . "::method{$m}"] = true;
    }
    $methodCacheProp->setValue(null, $methodCache);

    $reflectionCache = $reflectionCacheProp->getValue(null);
    $reflectionCache[$componentClass] = [];
    for ($p = 0; $p < 30; $p++) {
        $reflectionCache[$componentClass]["property{$p}"] = ['name' => "property{$p}", 'type' => 'mixed'];
    }
    $reflectionCacheProp->setValue(null, $reflectionCache);

    // Redirector stack imbalance
    if ($request % 5 === 0) {
        SupportRedirects::$redirectorCacheStack[] = new stdClass();
    }

    // End of request
    if (!$noFlush) {
        flushAllState();
    }

    if ($request % 50 === 0) {
        gc_collect_cycles();
        $mem = memory_get_usage(true);
        echo "Request {$request}: " . formatBytes($mem) . " (+" . formatBytes($mem - $startMemory) . ")\n";
    }
}

gc_collect_cycles();
$endMemory = memory_get_usage(true);

echo "\n=== RESULTS ===\n";
echo "Final: " . formatBytes($endMemory) . "\n";
echo "Growth: " . formatBytes($endMemory - $startMemory) . "\n";
echo "Per request: " . formatBytes(($endMemory - $startMemory) / $requests) . "\n";
echo "\nStatic arrays:\n";
echo "  componentStack: " . count(HandleComponents::$componentStack) . "\n";
echo "  renderStack: " . count(HandleComponents::$renderStack) . "\n";
echo "  redirectorCacheStack: " . count(SupportRedirects::$redirectorCacheStack) . "\n";
echo "  traitCache: " . count($traitCacheProp->getValue(null)) . "\n";
echo "  methodCache: " . count($methodCacheProp->getValue(null)) . "\n";
echo "  reflectionCache: " . count($reflectionCacheProp->getValue(null)) . "\n";

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
}
