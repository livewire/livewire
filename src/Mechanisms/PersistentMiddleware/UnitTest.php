<?php

namespace Livewire\Mechanisms\PersistentMiddleware;

use Illuminate\Support\Facades\Facade;
use Livewire\Attributes\Persistent;
use Livewire\Livewire;
use function Livewire\invade;

class UnitTest extends \LegacyTests\Unit\TestCase
{
    public function test_it_does_not_have_persistent_middleware_memory_leak_when_adding_middleware()
    {
        $base = Livewire::getPersistentMiddleware();
        Livewire::addPersistentMiddleware('MyMiddleware');

        $config = $this->app['config'];
        $this->app->forgetInstances();
        $this->app->forgetScopedInstances();
        Facade::clearResolvedInstances();
        // Need to rebind these for the testcase cleanup to work.
        $this->app->instance('app', $this->app);
        $this->app->instance('config', $config);

        // It hangs around because it is a static variable, so we do expect
        // it to still exist here.
        $this->assertSame([
            ...$base,
            'MyMiddleware',
        ], Livewire::getPersistentMiddleware());

        Livewire::addPersistentMiddleware('MyMiddleware');
        $this->assertSame([
            ...$base,
            'MyMiddleware',
        ], Livewire::getPersistentMiddleware());
    }
	
    public function test_it_can_filter_middleware_by_persistent_middleware_attribute()
    {
        $base = Livewire::getPersistentMiddleware();
		
        Livewire::addPersistentMiddleware([
			PersistentMiddlewareViaMethod::class,
			PersistentMiddlewareViaMethodAndAttribute::class,
        ]);
		
		$filteredPersistentMiddleware = invade(app(PersistentMiddleware::class))->filterMiddlewareByPersistentMiddleware([
			NonPersistentMiddleware::class,
			PersistentMiddlewareViaMethod::class,
			PersistentMiddlewareViaAttribute::class,
			PersistentMiddlewareViaParentClassAttribute::class,
			PersistentMiddlewareViaMethodAndAttribute::class,
		]);
		
		$this->assertSame([
			PersistentMiddlewareViaMethod::class,
			PersistentMiddlewareViaAttribute::class,
			PersistentMiddlewareViaParentClassAttribute::class,
			PersistentMiddlewareViaMethodAndAttribute::class
		], $filteredPersistentMiddleware);
    }
}

class NonPersistentMiddleware
{
	//
}

class PersistentMiddlewareViaMethod
{
	//
}

#[Persistent]
class PersistentMiddlewareViaAttribute
{
	//
}

class PersistentMiddlewareViaParentClassAttribute extends PersistentMiddlewareViaAttribute
{
	//
}

#[Persistent]
class PersistentMiddlewareViaMethodAndAttribute
{
	//
}
