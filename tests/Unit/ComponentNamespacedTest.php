<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Illuminate\Support\Facades\File;
use Illuminate\View\ViewFinderInterface;

class ComponentNamespacedTest extends TestCase
{
    public function makeACleanSlate()
    {
        parent::makeACleanSlate();

        File::deleteDirectory(base_path('Package'));
    }

    protected function moduleClassesPath($path = '')
    {
        return base_path('Package/src/Http/Livewire'.($path ? '/'.$path : ''));
    }

    protected function moduleViewsPath($path = '')
    {
        return base_path('Package/resources/views/livewire'.($path ? '/'.$path : ''));
    }

    /** @test */
    public function can_get_component_with_namespace_registeration()
    {
        File::makeDirectory($this->moduleClassesPath(), 0755, true);
        File::makeDirectory($this->moduleViewsPath(), 0755, true);

        File::put(
            $this->moduleClassesPath('DefaultNamespace.php'),
<<<EOT
<?php

namespace Package\Http\Livewire;

use Livewire\Component;

class DefaultNamespace extends Component {}
EOT
        );

        File::put(
            $this->moduleViewsPath('default-namespace.blade.php'),
<<<EOT
<div>I've been namespaced!</div>
EOT
        );

        include $this->moduleClassesPath('DefaultNamespace.php');

        app('livewire')->componentNamespace('Package\\Http\\Livewire', 'package');

        app('view')->addNamespace('package', $this->moduleViewsPath('..'));

        $component = Livewire::test('Package\Http\Livewire\DefaultNamespace');

        $this->assertEquals('package::default-namespace', $component->instance()->getPrefix().ViewFinderInterface::HINT_PATH_DELIMITER.$component->instance()->getName());
    }

    /** @test */
    public function can_get_child_component_with_namespace_registeration()
    {
        File::makeDirectory($this->moduleClassesPath('Custom'), 0755, true);
        File::makeDirectory($this->moduleViewsPath('custom'), 0755, true);

        File::put(
            $this->moduleClassesPath('Custom/DefaultNamespace.php'),
<<<EOT
<?php

namespace Package\Http\Livewire\Custom;

use Livewire\Component;

class DefaultNamespace extends Component {}
EOT
        );

        File::put(
            $this->moduleViewsPath('custom/default-namespace.blade.php'),
<<<EOT
<div>I've been namespaced!</div>
EOT
        );

        include $this->moduleClassesPath('Custom/DefaultNamespace.php');

        app('livewire')->componentNamespace('Package\\Http\\Livewire', 'package');

        app('view')->addNamespace('package', $this->moduleViewsPath('..'));

        $component = Livewire::test('Package\Http\Livewire\Custom\DefaultNamespace');

        $this->assertEquals('package::custom.default-namespace', $component->instance()->getPrefix().ViewFinderInterface::HINT_PATH_DELIMITER.$component->instance()->getName());
    }
}
