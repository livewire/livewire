<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DiscoverCommandTest extends TestCase
{
    /** @test */
    public function components_that_are_created_manually_can_be_added_to_the_manifest()
    {
        // Make the class & view directories, because otherwise, the manifest file cannot be created.
        File::makeDirectory($this->livewireClassesPath());
        File::makeDirectory($this->livewireViewsPath());

        // Ensure theres a manifest file that will become stale.
        Artisan::call('livewire:discover');

        // Manually create the Livewire component.
        File::put(
            $this->livewireClassesPath('ToBeDiscovered.php'),
<<<EOT
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ToBeDiscovered extends Component {
    public function render() { return view('livewire.to-be-discovered'); }
}
EOT
        );

        File::put(
            $this->livewireViewsPath('to-be-discovered.blade.php'),
<<<'EOT'
<div>I've been discovered!</div>
EOT
        );

        // We will get an error when trying to find it because the manifest is stale.
        try {
            view('render-component', [
                'component' => 'to-be-discovered',
            ])->render();
        } catch (\Exception $e) {
            $this->assertContains('Unable to find component: [to-be-discovered]', $e->getMessage());
        }

        Artisan::call('livewire:discover');

        $output = view('render-component', [
            'component' => 'to-be-discovered',
        ])->render();

        $this->assertContains('I\'ve been discovered!', $output);
    }

    /** @test */
    public function the_manifest_file_is_automatically_created_if_none_exists()
    {
        $manifestPath = app()->bootstrapPath('cache/livewire-components.php');

        // I'm calling "make:livewire" as a shortcut to generate a manifest file
        Artisan::call('make:livewire foo');

        File::delete($manifestPath);

        // We need to refresh the appliction because otherwise, the manifest
        // will still be stored in the object memory.
        $this->refreshApplication();

        // Attempting to render a component should re-generate the manifest file.
        view('render-component', [
            'component' => 'foo',
        ])->render();

        $this->assertTrue(File::exists($manifestPath));
    }
}
