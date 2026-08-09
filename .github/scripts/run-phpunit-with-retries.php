<?php

/**
 * Run PHPUnit and retry only the test cases that failed on the first run.
 *
 * Usage: php run-phpunit-with-retries.php [phpunit arguments...]
 */

$phpunit = __DIR__.'/../../vendor/bin/phpunit';
$report = tempnam(sys_get_temp_dir(), 'livewire-phpunit-');

register_shutdown_function(function () use ($report) {
    if (file_exists($report)) unlink($report);
});

$exitCode = run([$phpunit, ...array_slice($argv, 1), '--log-junit='.$report]);

if ($exitCode === 0) exit(0);

$failedTests = failedTestsFrom($report);

if ($failedTests === []) {
    fwrite(STDERR, "\nPHPUnit failed before reporting an individual failed test.\n");

    exit($exitCode);
}

$count = count($failedTests);

fwrite(STDOUT, "\nRetrying {$count} failed ".($count === 1 ? 'test' : 'tests')." in fresh PHPUnit processes...\n\n");

$retryFailed = false;

foreach ($failedTests as $test) {
    $name = $test['class'].'::'.$test['name'];
    $filter = '/^'.preg_quote($name, '/').'$/D';

    fwrite(STDOUT, "Retrying {$name}\n");

    if (run([$phpunit, $test['file'], '--filter', $filter, '--do-not-cache-result']) !== 0) {
        $retryFailed = true;
    }
}

exit($retryFailed ? 1 : 0);

function failedTestsFrom(string $report): array
{
    if (! file_exists($report)) return [];

    $document = new DOMDocument;

    if (! @$document->load($report)) return [];

    $tests = [];

    foreach ((new DOMXPath($document))->query('//testcase[failure or error]') as $testCase) {
        $file = $testCase->getAttribute('file');
        $class = $testCase->getAttribute('class');
        $name = $testCase->getAttribute('name');

        if ($file === '' || $class === '' || $name === '') continue;

        $tests[$class.'::'.$name] = compact('file', 'class', 'name');
    }

    return array_values($tests);
}

function run(array $command): int
{
    passthru(implode(' ', array_map('escapeshellarg', $command)), $exitCode);

    return $exitCode;
}
