<?php

namespace Livewire\Mechanisms\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\WithConsoleEvents;
use Livewire\Livewire;
use Symfony\Component\Console\Output\BufferedOutput;

class CacheCompiledFilesUnitTest extends \Tests\TestCase
{
    use WithConsoleEvents;

    public function test_view_cache_compiles_a_single_file_component_with_its_view_and_islands()
    {
        $directory = __DIR__ . '/../../Features/SupportIslands/fixtures';
        $path = $directory . '/sfc-island-imports.blade.php';

        Livewire::addLocation(viewPath: $directory);

        Artisan::call('view:cache');

        $cacheManager = app('livewire.compiler')->cacheManager;

        $this->assertTrue($cacheManager->hasBeenCompiled($path));
        $this->assertFalse(Blade::isExpired($cacheManager->getViewPath($path)));

        $this->assertIslandsAreCompiled();
    }

    public function test_compiling_the_generated_views_again_compiles_the_islands_their_views_write_again()
    {
        Livewire::addLocation(viewPath: __DIR__ . '/../../Features/SupportIslands/fixtures');

        $compiler = app('livewire.compiler');

        $compiler->compileAll(app('livewire.finder')->getViewBasedComponentPaths());
        $compiler->compileGeneratedViews();

        // view:clear before Laravel 12.50 deletes the compiled views and keeps Livewire's directory...
        File::delete(glob(config('view.compiled') . '/*.php'));

        $compiler->compileGeneratedViews();

        $this->assertIslandsAreCompiled();
    }

    public function test_a_generated_view_that_fails_to_compile_leaves_the_others_compiled()
    {
        Livewire::addLocation(viewPath: __DIR__ . '/../../Features/SupportIslands/fixtures');

        $compiler = app('livewire.compiler');

        $compiler->compileAll(app('livewire.finder')->getViewBasedComponentPaths());

        // The view of a deleted component, named to compile before the others...
        $stale = $compiler->cacheManager->cacheDirectory . '/views/00000000.blade.php';
        File::put($stale, '<div><x-deleted-widget /></div>');

        $compiler->compileGeneratedViews($output = new BufferedOutput);

        $this->assertStringContainsString($stale, $output->fetch());
        $this->assertIslandsAreCompiled();
    }

    public function test_view_cache_compiles_livewire_only_directives_into_a_component_view()
    {
        Livewire::directive('precompiled', fn () => 'directive output');

        File::ensureDirectoryExists($this->livewireComponentsPath());
        File::put($this->livewireComponentsPath('directive-probe.blade.php'), <<<'BLADE'
        <?php

        new class extends Livewire\Component {};
        ?>

        <div>@precompiled</div>
        BLADE);

        // Backdate the source, or the request reads it as changed and compiles it again...
        touch($this->livewireComponentsPath('directive-probe.blade.php'), time() - 10);

        Artisan::call('view:cache');

        Livewire::test('directive-probe')->assertSee('directive output');
    }

    protected function assertIslandsAreCompiled()
    {
        $islands = File::files(app('livewire.compiler')->cacheManager->cacheDirectory . '/islands');

        $this->assertNotEmpty($islands);

        foreach ($islands as $island) {
            $this->assertFalse(Blade::isExpired($island->getPathname()));
        }
    }
}
