<?php
/**
 * Distributes browser test files across shards using greedy bin-packing.
 * Usage: php get-browser-test-shard.php <shard_index> <total_shards>
 */

$shardIndex = (int) ($argv[1] ?? 0);
$totalShards = (int) ($argv[2] ?? 4);

$files = [];
$srcDir = __DIR__ . '/../../src';

// Recursively find all *BrowserTest.php files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && preg_match('/BrowserTest\.php$/', $file->getFilename())) {
        $content = file_get_contents($file->getPathname());
        preg_match_all('/function\s+test_/m', $content, $matches);
        $files[] = ['path' => $file->getRealPath(), 'tests' => count($matches[0])];
    }
}

usort($files, fn($a, $b) => $b['tests'] <=> $a['tests']);

$shards = array_fill(0, $totalShards, ['files' => [], 'weight' => 0]);
foreach ($files as $file) {
    $minIdx = 0;
    for ($i = 1; $i < $totalShards; $i++) {
        if ($shards[$i]['weight'] < $shards[$minIdx]['weight']) {
            $minIdx = $i;
        }
    }
    $shards[$minIdx]['files'][] = $file['path'];
    $shards[$minIdx]['weight'] += $file['tests'];
}

echo implode(' ', $shards[$shardIndex]['files']);
