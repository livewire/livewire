<?php

namespace Livewire\Features\SupportBetterExceptions;

use Illuminate\Support\Facades\File;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Orchestra\Testbench\TestCase;

class UnitTest extends TestCase
{
    protected $tempPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->tempPath = sys_get_temp_dir() . '/livewire_mapper_test_' . uniqid();
        File::makeDirectory($this->tempPath, 0755, true);
        File::makeDirectory($this->tempPath . '/livewire/classes', 0755, true);
        File::makeDirectory($this->tempPath . '/livewire/views', 0755, true);
    }

    protected function tearDown(): void
    {
        if (File::exists($this->tempPath)) {
            File::deleteDirectory($this->tempPath);
        }

        LivewireSourceMapper::clearCache();

        parent::tearDown();
    }

    public function test_can_parse_php_source_comment()
    {
        // Create a compiled class file with source comment
        $compiledPath = $this->tempPath . '/livewire/classes/abc123.php';
        $originalPath = '/app/resources/views/livewire/counter.blade.php';

        File::put($compiledPath, <<<PHP
<?php

/** @livewireSource {$originalPath}:5 */
use Livewire\Component;

return new class extends Component
{
    public \$count = 0;
};
PHP
        );

        $mapper = new LivewireSourceMapper($this->tempPath . '/livewire');

        // Create a fake exception with the compiled path
        $exception = FlattenException::createFromThrowable(new \Exception('Test'));

        // Set the trace to include our compiled file
        $reflection = new \ReflectionClass($exception);
        $traceProp = $reflection->getProperty('trace');
        $traceProp->setAccessible(true);
        $traceProp->setValue($exception, [
            ['file' => $compiledPath, 'line' => 8, 'function' => 'test'],
        ]);

        // Map the exception
        $mappedException = $mapper->map($exception);
        $trace = $mappedException->getTrace();

        // The file should now point to the original source
        $this->assertEquals($originalPath, $trace[0]['file']);
    }

    public function test_can_parse_blade_source_comment()
    {
        // Create a compiled view file with source comment
        $compiledPath = $this->tempPath . '/livewire/views/abc123.blade.php';
        $originalPath = '/app/resources/views/livewire/counter.blade.php';

        File::put($compiledPath, <<<BLADE
{{-- @livewireSource {$originalPath}:15 --}}
<?php
use Livewire\Component;
?>

<div>{{ \$count }}</div>
BLADE
        );

        $mapper = new LivewireSourceMapper($this->tempPath . '/livewire');

        // Create a fake exception with the compiled path
        $exception = FlattenException::createFromThrowable(new \Exception('Test'));

        // Set the trace to include our compiled file
        $reflection = new \ReflectionClass($exception);
        $traceProp = $reflection->getProperty('trace');
        $traceProp->setAccessible(true);
        $traceProp->setValue($exception, [
            ['file' => $compiledPath, 'line' => 5, 'function' => 'test'],
        ]);

        // Map the exception
        $mappedException = $mapper->map($exception);
        $trace = $mappedException->getTrace();

        // The file should now point to the original source
        $this->assertEquals($originalPath, $trace[0]['file']);
    }

    public function test_non_livewire_files_are_unchanged()
    {
        $mapper = new LivewireSourceMapper($this->tempPath . '/livewire');

        // Create a fake exception with a non-Livewire file
        $exception = FlattenException::createFromThrowable(new \Exception('Test'));

        $originalFile = '/app/Http/Controllers/TestController.php';

        $reflection = new \ReflectionClass($exception);
        $traceProp = $reflection->getProperty('trace');
        $traceProp->setAccessible(true);
        $traceProp->setValue($exception, [
            ['file' => $originalFile, 'line' => 25, 'function' => 'test'],
        ]);

        // Map the exception
        $mappedException = $mapper->map($exception);
        $trace = $mappedException->getTrace();

        // The file should remain unchanged
        $this->assertEquals($originalFile, $trace[0]['file']);
        $this->assertEquals(25, $trace[0]['line']);
    }

    public function test_compiled_file_without_source_comment_is_unchanged()
    {
        // Create a compiled file WITHOUT source comment
        $compiledPath = $this->tempPath . '/livewire/classes/xyz789.php';

        File::put($compiledPath, <<<'PHP'
<?php

use Livewire\Component;

return new class extends Component
{
    public $count = 0;
};
PHP
        );

        $mapper = new LivewireSourceMapper($this->tempPath . '/livewire');

        $exception = FlattenException::createFromThrowable(new \Exception('Test'));

        $reflection = new \ReflectionClass($exception);
        $traceProp = $reflection->getProperty('trace');
        $traceProp->setAccessible(true);
        $traceProp->setValue($exception, [
            ['file' => $compiledPath, 'line' => 5, 'function' => 'test'],
        ]);

        $mappedException = $mapper->map($exception);
        $trace = $mappedException->getTrace();

        // The file should remain as the compiled path since no source comment exists
        $this->assertEquals($compiledPath, $trace[0]['file']);
    }
}
