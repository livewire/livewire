<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class ConvertCommandUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Ensure components are cleared before each test...
        $this->makeACleanSlate();
    }

    public function test_single_file_component_can_be_converted_to_multi_file()
    {
        // Create a single-file component
        Artisan::call('make:livewire', ['name' => 'foo']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));

        // Convert to multi-file
        $exitCode = Artisan::call('livewire:convert', ['name' => 'foo', '--mfc' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
    }

    public function test_multi_file_component_can_be_converted_to_single_file()
    {
        // Create a multi-file component
        Artisan::call('make:livewire', ['name' => 'bar', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡bar')));

        // Convert to single-file
        $exitCode = Artisan::call('livewire:convert', ['name' => 'bar', '--sfc' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡bar')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar.blade.php')));
    }

    public function test_auto_detects_and_converts_to_opposite_format()
    {
        // Create a single-file component
        Artisan::call('make:livewire', ['name' => 'auto-test']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡auto-test.blade.php')));

        // Convert without specifying format (should auto-detect and convert to MFC)
        $exitCode = Artisan::call('livewire:convert', ['name' => 'auto-test']);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡auto-test')));
    }

    public function test_converts_with_javascript_content()
    {
        // Manually create an SFC with JavaScript
        $sfcPath = $this->livewireComponentsPath('⚡with-js.blade.php');
        File::ensureDirectoryExists(dirname($sfcPath));
        File::put($sfcPath, <<<'BLADE'
<?php

use Livewire\Component;

new class extends Component
{
    public $count = 0;
};
?>

<div>
    <button wire:click="$set('count', $count + 1)">Increment</button>
    <span>{{ $count }}</span>
</div>

<script>
console.log('Hello from JavaScript');
</script>
BLADE
        );

        // Convert to multi-file
        $exitCode = Artisan::call('livewire:convert', ['name' => 'with-js', '--mfc' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡with-js/with-js.js')));
        $jsContent = File::get($this->livewireComponentsPath('⚡with-js/with-js.js'));
        $this->assertStringContainsString("console.log('Hello from JavaScript')", $jsContent);
    }

    public function test_preserves_component_logic_during_conversion()
    {
        // Create an SFC with custom logic
        $sfcPath = $this->livewireComponentsPath('⚡custom-logic.blade.php');
        File::ensureDirectoryExists(dirname($sfcPath));
        File::put($sfcPath, <<<'BLADE'
<?php

use Livewire\Component;

new class extends Component
{
    public $name = 'World';

    public function greet()
    {
        return "Hello, {$this->name}!";
    }
};
?>

<div>
    <h1>{{ $this->greet() }}</h1>
</div>
BLADE
        );

        // Convert to MFC
        Artisan::call('livewire:convert', ['name' => 'custom-logic', '--mfc' => true]);

        // Verify the class content
        $classContent = File::get($this->livewireComponentsPath('⚡custom-logic/custom-logic.php'));
        $this->assertStringContainsString('public $name = \'World\'', $classContent);
        $this->assertStringContainsString('public function greet()', $classContent);

        // Convert back to SFC
        Artisan::call('livewire:convert', ['name' => 'custom-logic', '--sfc' => true]);

        // Verify the content is preserved
        $sfcContent = File::get($this->livewireComponentsPath('⚡custom-logic.blade.php'));
        $this->assertStringContainsString('public $name = \'World\'', $sfcContent);
        $this->assertStringContainsString('public function greet()', $sfcContent);
    }

    public function test_error_when_component_not_found()
    {
        $exitCode = Artisan::call('livewire:convert', ['name' => 'non-existent']);

        $this->assertEquals(1, $exitCode);
    }

    public function test_handles_nested_components()
    {
        // Create a nested single-file component
        Artisan::call('make:livewire', ['name' => 'admin.user-form']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡user-form.blade.php')));

        // Convert to multi-file
        $exitCode = Artisan::call('livewire:convert', ['name' => 'admin.user-form', '--mfc' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('admin/⚡user-form')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡user-form/user-form.php')));
    }

    public function test_respects_emoji_configuration()
    {
        $this->app['config']->set('livewire.make_command.emoji', false);

        // Create a single-file component without emoji
        Artisan::call('make:livewire', ['name' => 'no-emoji']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('no-emoji.blade.php')));

        // Convert to multi-file
        Artisan::call('livewire:convert', ['name' => 'no-emoji', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('no-emoji')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('no-emoji/no-emoji.php')));
    }

    public function test_test_file_created_when_test_flag_provided()
    {
        // Create a single-file component
        Artisan::call('make:livewire', ['name' => 'with-test']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡with-test.blade.php')));

        // Convert to multi-file with --test flag
        $exitCode = Artisan::call('livewire:convert', ['name' => 'with-test', '--mfc' => true, '--test' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡with-test/with-test.test.php')));
    }
}
