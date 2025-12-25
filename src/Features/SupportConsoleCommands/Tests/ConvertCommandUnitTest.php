<?php

namespace Livewire\Features\SupportConsoleCommands\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ConvertCommandUnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->makeACleanSlate();
    }

    // ==========================================
    // Error Cases
    // ==========================================

    public function test_convert_fails_when_component_not_found()
    {
        $exitCode = Artisan::call('livewire:convert', ['name' => 'non-existent', '--sfc' => true]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_convert_fails_when_already_in_target_format_sfc()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));

        $exitCode = Artisan::call('livewire:convert', ['name' => 'foo', '--sfc' => true]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_convert_fails_when_already_in_target_format_mfc()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));

        $exitCode = Artisan::call('livewire:convert', ['name' => 'foo', '--mfc' => true]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_convert_fails_when_already_in_target_format_class()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));

        $exitCode = Artisan::call('livewire:convert', ['name' => 'foo', '--class' => true]);

        $this->assertEquals(1, $exitCode);
    }

    // ==========================================
    // SFC -> MFC Conversions
    // ==========================================

    public function test_convert_sfc_to_mfc()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'foo', '--mfc' => true]);

        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
    }

    public function test_convert_sfc_to_mfc_preserves_test_file()
    {
        // Create SFC with test
        Artisan::call('make:livewire', ['name' => 'foo', '--test' => true]);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.test.php')));

        // Convert to MFC
        Artisan::call('livewire:convert', ['name' => 'foo', '--mfc' => true]);

        // Check that test file was moved into MFC directory
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.test.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo.test.php')));

        $testContent = File::get($this->livewireComponentsPath('⚡foo/foo.test.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
    }

    public function test_convert_sfc_to_mfc_creates_test_with_flag()
    {
        // Create SFC without test
        Artisan::call('make:livewire', ['name' => 'qux']);
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡qux.test.php')));

        // Convert to MFC with --test flag
        Artisan::call('livewire:convert', ['name' => 'qux', '--mfc' => true, '--test' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡qux/qux.test.php')));
    }

    public function test_convert_sfc_to_mfc_without_test_flag_preserves_existing_test()
    {
        // Create SFC with test
        Artisan::call('make:livewire', ['name' => 'baz', '--test' => true]);

        // Convert to MFC without --test flag (should still preserve test)
        Artisan::call('livewire:convert', ['name' => 'baz', '--mfc' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡baz/baz.test.php')));
    }

    public function test_convert_sfc_to_mfc_without_emoji()
    {
        $this->app['config']->set('livewire.make_command.emoji', false);

        Artisan::call('make:livewire', ['name' => 'foo']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('foo.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'foo', '--mfc' => true]);

        $this->assertFalse(File::exists($this->livewireComponentsPath('foo.blade.php')));
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('foo/foo.blade.php')));
    }

    public function test_convert_nested_sfc_to_mfc()
    {
        Artisan::call('make:livewire', ['name' => 'admin.dashboard']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡dashboard.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'admin.dashboard', '--mfc' => true]);

        $this->assertFalse(File::exists($this->livewireComponentsPath('admin/⚡dashboard.blade.php')));
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('admin/⚡dashboard')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡dashboard/dashboard.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡dashboard/dashboard.blade.php')));
    }

    // ==========================================
    // MFC -> SFC Conversions
    // ==========================================

    public function test_convert_mfc_to_sfc()
    {
        Artisan::call('make:livewire', ['name' => 'bar', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡bar')));

        Artisan::call('livewire:convert', ['name' => 'bar', '--sfc' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar.blade.php')));
        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡bar')));
    }

    public function test_convert_mfc_to_sfc_preserves_test_file()
    {
        // Create MFC with test
        Artisan::call('make:livewire', ['name' => 'bar', '--mfc' => true, '--test' => true]);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar/bar.test.php')));

        // Convert to SFC
        Artisan::call('livewire:convert', ['name' => 'bar', '--sfc' => true]);

        // Check that test file was moved out of MFC directory
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar.test.php')));
        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡bar')));

        $testContent = File::get($this->livewireComponentsPath('⚡bar.test.php'));
        $this->assertStringContainsString("it('renders successfully'", $testContent);
    }

    public function test_convert_mfc_to_sfc_without_test_file_works()
    {
        // Create MFC without test
        Artisan::call('make:livewire', ['name' => 'quux', '--mfc' => true]);
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡quux/quux.test.php')));

        // Convert to SFC
        Artisan::call('livewire:convert', ['name' => 'quux', '--sfc' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡quux.blade.php')));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡quux.test.php')));
    }

    public function test_convert_mfc_to_sfc_without_emoji()
    {
        $this->app['config']->set('livewire.make_command.emoji', false);

        Artisan::call('make:livewire', ['name' => 'bar', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('bar')));

        Artisan::call('livewire:convert', ['name' => 'bar', '--sfc' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('bar.blade.php')));
        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('bar')));
    }

    public function test_convert_nested_mfc_to_sfc()
    {
        Artisan::call('make:livewire', ['name' => 'admin.settings', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('admin/⚡settings')));

        Artisan::call('livewire:convert', ['name' => 'admin.settings', '--sfc' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡settings.blade.php')));
        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('admin/⚡settings')));
    }

    // ==========================================
    // Class -> SFC Conversions
    // ==========================================

    public function test_convert_class_to_sfc()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'foo', '--sfc' => true]);

        $this->assertFalse(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));

        $sfcContent = File::get($this->livewireComponentsPath('⚡foo.blade.php'));
        $this->assertStringContainsString('<?php', $sfcContent);
        $this->assertStringContainsString('new class extends Component', $sfcContent);
        $this->assertStringContainsString('<div>', $sfcContent);
    }

    public function test_convert_class_to_sfc_with_page_flag()
    {
        Artisan::call('make:livewire', ['name' => 'dashboard', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Dashboard.php')));

        Artisan::call('livewire:convert', ['name' => 'dashboard', '--sfc' => true, '--page' => true]);

        $this->assertFalse(File::exists($this->livewireClassesPath('Dashboard.php')));
        $this->assertTrue(File::exists(resource_path('views/pages/⚡dashboard.blade.php')));
    }

    public function test_convert_nested_class_to_sfc()
    {
        Artisan::call('make:livewire', ['name' => 'admin.users', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Admin/Users.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('admin/users.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'admin.users', '--sfc' => true]);

        $this->assertFalse(File::exists($this->livewireClassesPath('Admin/Users.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('admin/users.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡users.blade.php')));
    }

    // ==========================================
    // Class -> MFC Conversions
    // ==========================================

    public function test_convert_class_to_mfc()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'foo', '--mfc' => true]);

        $this->assertFalse(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('foo.blade.php')));
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo/foo.blade.php')));
    }

    public function test_convert_class_to_mfc_creates_test_with_flag()
    {
        Artisan::call('make:livewire', ['name' => 'bar', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Bar.php')));

        Artisan::call('livewire:convert', ['name' => 'bar', '--mfc' => true, '--test' => true]);

        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar/bar.test.php')));
    }

    public function test_convert_class_to_mfc_with_page_flag()
    {
        Artisan::call('make:livewire', ['name' => 'dashboard', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Dashboard.php')));

        Artisan::call('livewire:convert', ['name' => 'dashboard', '--mfc' => true, '--page' => true]);

        $this->assertFalse(File::exists($this->livewireClassesPath('Dashboard.php')));
        $this->assertTrue(File::isDirectory(resource_path('views/pages/⚡dashboard')));
        $this->assertTrue(File::exists(resource_path('views/pages/⚡dashboard/dashboard.php')));
        $this->assertTrue(File::exists(resource_path('views/pages/⚡dashboard/dashboard.blade.php')));
    }

    public function test_convert_nested_class_to_mfc()
    {
        Artisan::call('make:livewire', ['name' => 'admin.settings', '--class' => true]);
        $this->assertTrue(File::exists($this->livewireClassesPath('Admin/Settings.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('admin/settings.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'admin.settings', '--mfc' => true]);

        $this->assertFalse(File::exists($this->livewireClassesPath('Admin/Settings.php')));
        $this->assertFalse(File::exists($this->livewireViewsPath('admin/settings.blade.php')));
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('admin/⚡settings')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡settings/settings.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡settings/settings.blade.php')));
    }

    // ==========================================
    // SFC -> Class Conversions
    // ==========================================

    public function test_convert_sfc_to_class()
    {
        Artisan::call('make:livewire', ['name' => 'foo']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'foo', '--class' => true]);

        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡foo.blade.php')));
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));

        $classContent = File::get($this->livewireClassesPath('Foo.php'));
        $this->assertStringContainsString('namespace App\Livewire;', $classContent);
        $this->assertStringContainsString('class Foo extends Component', $classContent);
        $this->assertStringContainsString("view('livewire.foo')", $classContent);
    }

    public function test_convert_sfc_to_class_preserves_test_file()
    {
        Artisan::call('make:livewire', ['name' => 'bar', '--test' => true]);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar.blade.php')));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar.test.php')));

        Artisan::call('livewire:convert', ['name' => 'bar', '--class' => true]);

        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡bar.test.php')));
        $this->assertTrue(File::exists($this->livewireClassesPath('Bar.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BarTest.php')));
    }

    public function test_convert_nested_sfc_to_class()
    {
        Artisan::call('make:livewire', ['name' => 'admin.dashboard']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('admin/⚡dashboard.blade.php')));

        Artisan::call('livewire:convert', ['name' => 'admin.dashboard', '--class' => true]);

        $this->assertFalse(File::exists($this->livewireComponentsPath('admin/⚡dashboard.blade.php')));
        $this->assertTrue(File::exists($this->livewireClassesPath('Admin/Dashboard.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('admin/dashboard.blade.php')));

        $classContent = File::get($this->livewireClassesPath('Admin/Dashboard.php'));
        $this->assertStringContainsString('namespace App\Livewire\Admin;', $classContent);
        $this->assertStringContainsString('class Dashboard extends Component', $classContent);
    }

    // ==========================================
    // MFC -> Class Conversions
    // ==========================================

    public function test_convert_mfc_to_class()
    {
        Artisan::call('make:livewire', ['name' => 'foo', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡foo')));

        Artisan::call('livewire:convert', ['name' => 'foo', '--class' => true]);

        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡foo')));
        $this->assertTrue(File::exists($this->livewireClassesPath('Foo.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('foo.blade.php')));

        $classContent = File::get($this->livewireClassesPath('Foo.php'));
        $this->assertStringContainsString('namespace App\Livewire;', $classContent);
        $this->assertStringContainsString('class Foo extends Component', $classContent);
    }

    public function test_convert_mfc_to_class_preserves_test_file()
    {
        Artisan::call('make:livewire', ['name' => 'bar', '--mfc' => true, '--test' => true]);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡bar/bar.test.php')));

        Artisan::call('livewire:convert', ['name' => 'bar', '--class' => true]);

        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡bar')));
        $this->assertTrue(File::exists($this->livewireClassesPath('Bar.php')));
        $this->assertTrue(File::exists($this->livewireTestsPath('BarTest.php')));
    }

    public function test_convert_nested_mfc_to_class()
    {
        Artisan::call('make:livewire', ['name' => 'admin.settings', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('admin/⚡settings')));

        Artisan::call('livewire:convert', ['name' => 'admin.settings', '--class' => true]);

        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('admin/⚡settings')));
        $this->assertTrue(File::exists($this->livewireClassesPath('Admin/Settings.php')));
        $this->assertTrue(File::exists($this->livewireViewsPath('admin/settings.blade.php')));
    }

    // ==========================================
    // Round-trip Content Preservation Tests
    // ==========================================

    /**
     * Normalize content for comparison by removing formatting differences
     * that don't affect functionality (trailing semicolons on anonymous classes,
     * whitespace normalization, etc.)
     */
    protected function normalizeContent(string $content): string
    {
        // Normalize line endings
        $content = str_replace("\r\n", "\n", $content);

        // Normalize multiple blank lines to single blank line
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        // Trim trailing whitespace from each line
        $content = implode("\n", array_map('rtrim', explode("\n", $content)));

        // Trim overall
        return trim($content);
    }

    public function test_sfc_to_mfc_to_sfc_roundtrip_preserves_content()
    {
        // Create an SFC with real content
        $originalContent = <<<'BLADE'
<?php

use Livewire\Component;
use App\Models\User;

new class extends Component
{
    public string $name = '';
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function decrement(): void
    {
        $this->count--;
    }

    public function updatedName($value): void
    {
        $this->name = strtoupper($value);
    }
};
?>

<div>
    <h1>Counter: {{ $count }}</h1>
    <p>Name: {{ $name }}</p>

    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>

    <input wire:model="name" type="text" />
</div>
BLADE;

        // Write the SFC directly
        $sfcPath = $this->livewireComponentsPath('⚡counter.blade.php');
        File::ensureDirectoryExists(dirname($sfcPath));
        File::put($sfcPath, $originalContent);

        $this->assertTrue(File::exists($sfcPath));

        // Convert SFC -> MFC
        Artisan::call('livewire:convert', ['name' => 'counter', '--mfc' => true]);

        $this->assertFalse(File::exists($sfcPath));
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡counter')));

        // Convert MFC -> SFC
        Artisan::call('livewire:convert', ['name' => 'counter', '--sfc' => true]);

        $this->assertTrue(File::exists($sfcPath));
        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡counter')));

        // Compare content (normalized to ignore formatting differences)
        $finalContent = File::get($sfcPath);
        $this->assertEquals(
            $this->normalizeContent($originalContent),
            $this->normalizeContent($finalContent),
            'Round-trip conversion should preserve content'
        );
    }

    public function test_mfc_to_sfc_to_mfc_roundtrip_preserves_content()
    {
        // Create an MFC with real content
        $originalClassContent = <<<'PHP'
<?php

use Livewire\Component;

new class extends Component
{
    public array $items = [];

    public function addItem(string $item): void
    {
        $this->items[] = $item;
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }
};
PHP;

        $originalViewContent = <<<'BLADE'
<div>
    <ul>
        @foreach($items as $index => $item)
            <li>
                {{ $item }}
                <button wire:click="removeItem({{ $index }})">Remove</button>
            </li>
        @endforeach
    </ul>
</div>
BLADE;

        // Write the MFC directly
        $mfcDir = $this->livewireComponentsPath('⚡todo-list');
        File::ensureDirectoryExists($mfcDir);
        File::put($mfcDir . '/todo-list.php', $originalClassContent);
        File::put($mfcDir . '/todo-list.blade.php', $originalViewContent);

        $this->assertTrue(File::isDirectory($mfcDir));

        // Convert MFC -> SFC
        Artisan::call('livewire:convert', ['name' => 'todo-list', '--sfc' => true]);

        $this->assertFalse(File::isDirectory($mfcDir));
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡todo-list.blade.php')));

        // Convert SFC -> MFC
        Artisan::call('livewire:convert', ['name' => 'todo-list', '--mfc' => true]);

        $this->assertTrue(File::isDirectory($mfcDir));
        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡todo-list.blade.php')));

        // Compare content (normalized to ignore formatting differences)
        $finalClassContent = File::get($mfcDir . '/todo-list.php');
        $finalViewContent = File::get($mfcDir . '/todo-list.blade.php');

        $this->assertEquals(
            $this->normalizeContent($originalClassContent),
            $this->normalizeContent($finalClassContent),
            'Round-trip should preserve class content'
        );
        $this->assertEquals(
            $this->normalizeContent($originalViewContent),
            $this->normalizeContent($finalViewContent),
            'Round-trip should preserve view content'
        );
    }

    // ==========================================
    // Page Flag Tests
    // ==========================================

    public function test_convert_sfc_to_mfc_with_page_flag()
    {
        // Create a regular SFC
        Artisan::call('make:livewire', ['name' => 'settings']);
        $this->assertTrue(File::exists($this->livewireComponentsPath('⚡settings.blade.php')));

        // Convert to MFC in pages namespace
        Artisan::call('livewire:convert', ['name' => 'settings', '--mfc' => true, '--page' => true]);

        $this->assertFalse(File::exists($this->livewireComponentsPath('⚡settings.blade.php')));
        $this->assertTrue(File::isDirectory(resource_path('views/pages/⚡settings')));
        $this->assertTrue(File::exists(resource_path('views/pages/⚡settings/settings.php')));
    }

    public function test_convert_mfc_to_sfc_with_page_flag()
    {
        // Create a regular MFC
        Artisan::call('make:livewire', ['name' => 'profile', '--mfc' => true]);
        $this->assertTrue(File::isDirectory($this->livewireComponentsPath('⚡profile')));

        // Convert to SFC in pages namespace
        Artisan::call('livewire:convert', ['name' => 'profile', '--sfc' => true, '--page' => true]);

        $this->assertFalse(File::isDirectory($this->livewireComponentsPath('⚡profile')));
        $this->assertTrue(File::exists(resource_path('views/pages/⚡profile.blade.php')));
    }
}
