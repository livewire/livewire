<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Livewire\Commands\ComponentParser;
use Livewire\LivewireComponentsFinder;

class ComponentNameAndNamespaceTest extends TestCase
{
    public function makeACleanSlate()
    {
        parent::makeACleanSlate();

        File::deleteDirectory(app_path('Custom'));
    }

    /** @test */
    public function can_get_name_with_livewire_default_namespace()
    {
        File::makeDirectory($this->livewireClassesPath('App'), 0755, true);
        File::makeDirectory($this->livewireViewsPath('app'), 0755, true);

        File::put(
            $this->livewireClassesPath('App/DefaultNamespace.php'),
<<<EOT
<?php

namespace App\Http\Livewire\App;

use Livewire\Component;

class DefaultNamespace extends Component {}
EOT
        );

        File::put(
            $this->livewireViewsPath('app/default-namespace.blade.php'),
<<<EOT
<div>I've been namespaced!</div>
EOT
        );

        $component = Livewire::test('App\Http\Livewire\App\DefaultNamespace');

        $this->assertEquals('app.default-namespace', $component->instance()->getName());
    }

    /** @test */
    public function can_get_name_with_custom_namespace()
    {
        config(['livewire.class_namespace' => 'Custom\\Controllers\\Http']);

        app()->instance(LivewireComponentsFinder::class, new LivewireComponentsFinder(
            new Filesystem,
            app()->bootstrapPath('cache/livewire-components.php'),
            ComponentParser::generatePathFromNamespace(config('livewire.class_namespace'))
        ));

        File::makeDirectory(app_path('Custom/Controllers/Http'), 0755, true);
        File::makeDirectory($this->livewireViewsPath());

        File::put(
            app_path('Custom/Controllers/Http') . '/CustomNamespace.php',
<<<EOT
<?php

namespace Custom\Controllers\Http;

use Livewire\Component;

class CustomNamespace extends Component {}
EOT
        );

        File::put(
            $this->livewireViewsPath('custom-namespace.blade.php'),
<<<EOT
<div>I've been namespaced!</div>
EOT
        );

        require(app_path('Custom/Controllers/Http') . '/CustomNamespace.php');
        $component = Livewire::test('Custom\Controllers\Http\CustomNamespace');

        $this->assertEquals('custom-namespace', $component->instance()->getName());
    }

    /** @test */
    public function can_get_name_with_app_namespace()
    {
        config(['livewire.class_namespace' => 'App']);
        $finder = new LivewireComponentsFinder(
            new Filesystem,
            app()->bootstrapPath('cache/livewire-components.php'),
            ComponentParser::generatePathFromNamespace(config('livewire.class_namespace'))
        );

        app()->instance(LivewireComponentsFinder::class, $finder);

        File::makeDirectory($this->livewireViewsPath());

        File::put(
            app_path() . '/AppNamespace.php',
<<<EOT
<?php

namespace App;

use Livewire\Component;

class AppNamespace extends Component {}
EOT
        );

        File::put(
            $this->livewireViewsPath('app-namespace.blade.php'),
            <<<EOT
<div>I've been namespaced!</div>
EOT
        );

        require(app_path('') . '/AppNamespace.php');
        $component = Livewire::test('App\AppNamespace');

        $this->assertEquals('app-namespace', $component->instance()->getName());
        $this->assertContains('App\AppNamespace', $finder->getClassNames());
    }
}
