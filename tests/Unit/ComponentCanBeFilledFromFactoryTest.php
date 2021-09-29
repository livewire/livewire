<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Livewire;

class ComponentCanBeFilledFromFactoryTest extends TestCase
{
    /** @test */
    public function can_fill_from_factory()
    {
        if(! class_exists(Factory::class)) {
            $this->markTestSkipped('Need Laravel >= 8');
        }

        $userFactory = ExampleUser::factory()->make();
        $component = Livewire::test(ComponentWithFactoryFillableProperties::class);

        $component->fillFactory($userFactory, 'user', ['password']);

        $component->assertSet('user.name', 'Caleb');
        $component->assertSet('user.email', 'example@laravel-livewire.com');
        $component->assertNotSet('user.password', 'test');
    }
}

if(class_exists(Factory::class)) {
    class UserFactory extends Factory
    {
        protected $model = ExampleUser::class;

        public function definition()
        {
            return [
                'name' => 'Caleb',
                'email' => 'example@laravel-livewire.com',
                'password' => 'test',
            ];
        }
    }

    class ExampleUser extends Model
    {
        use HasFactory;

        protected static function newFactory()
        {
            return UserFactory::new();
        }
    }

    class ComponentWithFactoryFillableProperties extends Component
    {
        public $user;

        protected $rules = [
            'user.name' => 'required',
            'user.email' => 'required|email',
        ];

        public function mount()
        {
            $this->user = new ExampleUser;
        }

        public function render()
        {
            return app('view')->make('null-view');
        }
    }
}
