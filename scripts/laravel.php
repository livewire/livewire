<?php

require __DIR__.'/../vendor/autoload.php';

$__commands = [];
$__name = '';
$__version = '';

class AppServiceProvider extends \Illuminate\Support\ServiceProvider
{
    function boot()
    {
        foreach ($GLOBALS['__commands'] as $command) {
            app(\Illuminate\Contracts\Console\Kernel::class)->registerCommand($command);
        }

        invade(app(\Illuminate\Contracts\Console\Kernel::class))->artisan->setName($GLOBALS['__name']);
        invade(app(\Illuminate\Contracts\Console\Kernel::class))->artisan->setVersion($GLOBALS['__version']);
    }
}

$root = sys_get_temp_dir().'/__lwcmdcache';

$cacheDir = $root.'/cache';
$viewsDir = $cacheDir.'/views';
if (! file_exists($cacheDir)) mkdir($cacheDir);
if (! file_exists($viewsDir)) mkdir($viewsDir);
$packagesCache = $cacheDir.'/packages.php';
file_put_contents($packagesCache, '<?php return [];');
$servicesCache = $cacheDir.'/services.php';
$appConfigCache = $cacheDir.'/config.php';
$routesCache = $cacheDir.'/routes.php';
$eventsCache = $cacheDir.'/events.php';
$_ENV['APP_PACKAGES_CACHE'] = $packagesCache;
$_ENV['APP_SERVICES_CACHE'] = $servicesCache;
$_ENV['APP_CONFIG_CACHE'] = $appConfigCache;
$_ENV['APP_ROUTES_CACHE'] = $routesCache;
$_ENV['APP_EVENTS_CACHE'] = $eventsCache;

$app = new Illuminate\Foundation\Application($root);
$app->singleton(Illuminate\Contracts\Console\Kernel::class, \Illuminate\Foundation\Console\Kernel::class);
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, Illuminate\Foundation\Exceptions\Handler::class);

$app->bind(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function () {
    return new class extends \Illuminate\Foundation\Bootstrap\LoadConfiguration {
        protected function loadConfigurationFiles($app, $repository)
        {
            $appConfig = [
                'name' => 'Laravel',
                'debug' => true,
                'url' => 'http://localhost',
                'timezone' => 'UTC',
                'locale' => 'en',
                'fallback_locale' => 'en',
                'faker_locale' => 'en_US',
                'key' => 'base64:Q7XKMi5sWNh2TNevn51dDjl67B/IyyqzptgAyE2rppU=',
                'cipher' => 'AES-256-CBC',
                'maintenance' => ['driver' => 'file'],
                'providers' => [
                    // Illuminate\Auth\AuthServiceProvider::class,
                    // Illuminate\Broadcasting\BroadcastServiceProvider::class,
                    // Illuminate\Bus\BusServiceProvider::class,
                    Illuminate\Cache\CacheServiceProvider::class,
                    // Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
                    // Illuminate\Cookie\CookieServiceProvider::class,
                    Illuminate\Database\DatabaseServiceProvider::class,
                    // Illuminate\Encryption\EncryptionServiceProvider::class,
                    Illuminate\Filesystem\FilesystemServiceProvider::class,
                    // Illuminate\Foundation\Providers\FoundationServiceProvider::class,
                    // Illuminate\Hashing\HashServiceProvider::class,
                    // Illuminate\Mail\MailServiceProvider::class,
                    // Illuminate\Notifications\NotificationServiceProvider::class,
                    // Illuminate\Pagination\PaginationServiceProvider::class,
                    // Illuminate\Pipeline\PipelineServiceProvider::class,
                    Illuminate\Queue\QueueServiceProvider::class,
                    // Illuminate\Redis\RedisServiceProvider::class,
                    // Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
                    // Illuminate\Session\SessionServiceProvider::class,
                    // Illuminate\Translation\TranslationServiceProvider::class,
                    // Illuminate\Validation\ValidationServiceProvider::class,
                    Illuminate\View\ViewServiceProvider::class,
                    AppServiceProvider::class,
                    // App\Providers\AppServiceProvider::class,
                    // App\Providers\AuthServiceProvider::class,
                    // App\Providers\BroadcastServiceProvider::class,
                    // App\Providers\EventServiceProvider::class,
                    // App\Providers\RouteServiceProvider::class,
                ],
                'aliases' => \Illuminate\Support\Facades\Facade::defaultAliases()->merge([
                    // 'ExampleClass' => App\Example\ExampleClass::class,
                ])->toArray(),
            ];
            $repository->set('app', $appConfig);
            $repository->set('view', ['compiled' => $GLOBALS['root'].'/cache/views']);
            $repository->set('database', [
                'default' => 'sqlite',
                'connections' => [
                    'sqlite' => [
                        'driver'   => 'sqlite',
                        'database' => ':memory:',
                        'prefix'   => '',
                    ],
                ]
            ]);
        }
    };
});

return [
    function ($name, $version = '') use (&$__name, &$__version) {
        $__name = $name;
        $__version = $version;
    },
    function ($name, $callback) use (&$__commands) {
        $command = new \Illuminate\Foundation\Console\ClosureCommand($name, $callback);

        $__commands[] = $command;

        return $command;
    },
    function () use (&$commands, $app) {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $input = new Symfony\Component\Console\Input\ArgvInput;
        $output = new Symfony\Component\Console\Output\ConsoleOutput;

        $status = $kernel->handle($input, $output);

        $kernel->terminate($input, $status);

        exit($status);
    },
];

function invade($obj)
{
    return new class($obj) {
        public $obj;
        public $reflected;

        public function __construct($obj)
        {
            $this->obj = $obj;
            $this->reflected = new ReflectionClass($obj);
        }

        public function __get($name)
        {
            $property = $this->reflected->getProperty($name);

            return $property->getValue($this->obj);
        }

        public function __set($name, $value)
        {
            $property = $this->reflected->getProperty($name);

            $property->setValue($this->obj, $value);
        }

        public function __call($name, $params)
        {
            $method = $this->reflected->getMethod($name);

            return $method->invoke($this->obj, ...$params);
        }
    };
}


