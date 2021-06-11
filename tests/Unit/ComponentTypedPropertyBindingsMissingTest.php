<?php


namespace Tests\Unit;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ComponentTypedPropertyBindingsMissingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        if (PHP_VERSION_ID < 70400) {
            $this->markTestSkipped('Only applies to PHP 7.4 and above.');
            return;
        }
        Schema::create('model_for_property_bindings', function ($table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    /** @test */
    public function missing_function_fire_if_route_prop_missing()
    {
        \Route::get('/foo/{parent}/{child}', ComponentWithPropAndMountBindings::class)
            ->missing(fn(Request $request) => redirect('/bar'));

        $this->get('/foo/bar/zap')->assertRedirect('/bar');
    }
}

class ModelForPropertyBinding extends Model
{
    protected $connection = 'testbench';
    protected $guarded = [];
}

class ComponentWithPropAndMountBindings extends Component
{
    public ModelForPropertyBinding $child;
    public $parent;

    public function mount(ModelForPropertyBinding $parent)
    {
        $this->parent = $parent;
    }

    public function render()
    {
        return 'foo';
    }
}
