<?php

namespace Tests;

use Livewire\Component;
use Livewire\LivewireManager;
use Illuminate\Foundation\Http\FormRequest;
use Livewire\Traits\InteractsWithLivewire;
use Illuminate\Auth\Access\AuthorizationException;

class ComponentValidatesDataWithFormRequest extends TestCase
{
    /** @test */
    public function can_validate_the_public_properties_of_the_component()
    {
        $component = app(LivewireManager::class)->test(ComponentWithRules::class);

        $component->call('submit');
        $component->assertSee('The foo field is required.');
    }

    /** @test */
    public function honours_the_authorization()
    {
        $component = app(LivewireManager::class)->test(ComponentWithAuthorization::class);

        $this->expectException(AuthorizationException::class);

        $component->call('submit');

        $component->assertNotSee('The foo field is required.');
    }
}

class RequestWithRules extends FormRequest
{
    use InteractsWithLivewire;

    public function rules()
    {
        return [
            'foo' => ['required'],
        ];
    }
}

class ComponentWithRules extends Component
{
    public $foo;

    public function submit()
    {
        $this->validate(RequestWithRules::class);
    }

    public function render()
    {
        return view('form-request');
    }
}

class RequestWithAuthorization extends FormRequest
{
    use InteractsWithLivewire;

    public function authorize() {
        return false;
    }

    public function rules()
    {
        return [
            'foo' => ['required'],
        ];
    }
}

class ComponentWithAuthorization extends Component
{
    public $foo;

    public function submit()
    {
        $this->validate(RequestWithAuthorization::class);
    }

    public function render()
    {
        return view('form-request');
    }
}
