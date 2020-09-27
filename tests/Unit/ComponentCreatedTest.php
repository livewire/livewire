<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;

class ComponentCreatedTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('dummy_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    /** @test */
    public function default_property_values_are_assigned_on_component_creation_and_used_during_mount()
    {
        Livewire::test(ComponentWithCreatedMethod::class)
            ->assertSet('foo', 'bar')
            ->assertSet('dummy.name', 'My Named Dummy');
    }

    /** @test */
    public function property_values_from_created_are_replicated_on_component_reset_but_mount_is_not_called()
    {
        Livewire::test(ComponentWithCreatedMethod::class)
            ->set('foo', 'bob')
            ->assertSet('dummy.name', 'My Named Dummy')
            ->call('resetAction')
            ->assertSet('foo', 'bar')
            ->assertSet('dummy.name', null);
    }

    /** @test */
    public function ensure_created_method_cant_be_called_as_an_action()
    {
        Livewire::test(ComponentWithCreatedMethod::class)
            ->set('dummy.name', 'Best Dummy Ever')
            ->call('created')
            ->assertSet('dummy.name', 'Best Dummy Ever');
    }
}

class DummyModel extends Model
{
    protected $connection = 'testbench';
    protected $guarded = false;
}

class ComponentWithCreatedMethod extends Component
{
    public $foo = 'bar';

    public $dummy;

    protected $rules = [
        'foo' => ['required'],
        'dummy.name' => ['required']
    ];

    public function created()
    {
        if (!isset($this->foo)) {
            // This shouldn't be reached.
            $this->foo = 'baz';
        }

        if (!isset($this->dummy)) {
            $this->dummy = DummyModel::make();
        }
    }

    public function mount()
    {
        if (!isset($this->foo)) {
            // This shouldn't be reached.
            $this->foo = 'zap';
        }

        $this->dummy->name = 'My Named Dummy';
    }

    public function resetAction()
    {
        parent::reset();
    }

    public function render()
    {
        return view('null-view');
    }
}
