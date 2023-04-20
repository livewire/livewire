<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Livewire\Controllers\HttpConnectionHandler;

class CookieTest extends TestCase
{
    /** @test */
    public function sets_laravel_cookie()
    {
        Route::get('/', function () {
            return 'Hello World';
        })->middleware('web');

        $this->withoutExceptionHandling()->withCookie('test', '1')->get('/');

        $handler = new CookieHttpConnectionHandler();
        $request = $handler->makeRequestFromUrlAndMethod('/');

        $this->assertSame('1', $request->cookie('test'));
    }
}

class CookieHttpConnectionHandler extends HttpConnectionHandler
{
    public function makeRequestFromUrlAndMethod($url, $method = 'GET')
    {
        return parent::makeRequestFromUrlAndMethod($url, $method);
    }
}
