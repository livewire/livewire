<?php

namespace Livewire;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void component($name, $class = null)
 * @method static void directive($name, $callback)
 * @method static \Livewire\Component new($name, $id = null)
 * @method static string mount($name, $params = [], $key = null)
 * @method static array snapshot($component)
 * @method static void fromSnapshot($snapshot)
 * @method static void listen($name, $callback)
 * @method static array update($snapshot, $diff, $calls)
 * @method static bool isLivewireRequest()
 * @method static void setUpdateRoute($callback)
 * @method static string getUpdateUri($callback)
 * @method static void setScriptRoute($callback)
 * @method static void useScriptTagAttributes($attributes)
 * @method static \Livewire\LivewireManager withUrlParams($params)
 * @method static \Livewire\LivewireManager withQueryParams($params)
 * @method static \Livewire\Features\SupportTesting\Testable test($name, $params = [])
 * @method static \Livewire\LivewireManager actingAs($user, $driver = null)
 * @method static bool isRunningServerless()
 * @method static void addPersistentMiddleware($middleware)
 * @method static void setPersistentMiddleware($middleware)
 * @method static array getPersistentMiddleware()
 * @method static void flushState()
 * @method static string originalUrl()
 * @method static string originalPath()
 * @method static string originalMethod()
 *
 * @see \Livewire\LivewireManager
 */
class Livewire extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'livewire';
    }
}
