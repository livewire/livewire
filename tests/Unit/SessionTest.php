<?php

namespace Tests\Unit;

use Livewire\Livewire;
use Livewire\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Session\Middleware\AuthenticateSession;
use Livewire\Controllers\HttpConnectionHandler;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Schema::create('model_for_sessions', function ($table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    /** @test */
    public function sets_laravel_session()
    {
        $model = ModelForSession::create();

        Route::get('/', function () {
            return 'Hello World';
        })->middleware(['web', AuthenticateSession::class]);

        $this->withoutExceptionHandling()->actingAs($model)->get('/');

        $handler = new Handler();
        $request = $handler->makeRequestFromUrlAndMethod('/');

        $this->assertTrue($request->hasSession());
        $this->assertInstanceOf(Store::class, $request->session());

        Str::startsWith($this->app->version(), '9.')
            ? $this->assertInstanceOf(SessionInterface::class, $request->getSession())
            : $this->assertInstanceOf(Store::class, $request->getSession());
    }
}

class ModelForSession extends Authenticatable
{
    protected $connection = 'testbench';
    protected $guarded = [];
}

class Handler extends HttpConnectionHandler
{
    public function makeRequestFromUrlAndMethod($url, $method = 'GET')
    {
        return parent::makeRequestFromUrlAndMethod($url, $method);
    }
}
