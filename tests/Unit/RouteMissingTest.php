<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class RouteMissingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (PHP_VERSION_ID < 70400) {
            $this->markTestSkipped('Only applies to PHP 7.4 and above.');
        }
    }

    /** @test */
    public function route_supports_laravels_missing_fallback_function(): void
    {
        Route::get('awesome-js/{framework}', ComponentWithModel::class)
             ->missing(function (Request $request) {
                 $this->assertEquals(request(), $request);
                 return redirect()->to('awesome-js/alpine');
             });

        $this->get('/awesome-js/jquery')->assertRedirect('/awesome-js/alpine');
    }
}

class FrameworkModel extends Model
{
    public function resolveRouteBinding($value, $field = null)
    {
        throw new ModelNotFoundException;
    }
}

class ComponentWithModel extends Component
{
    public FrameworkModel $framework;
}
