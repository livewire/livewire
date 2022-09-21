<?php

namespace Synthetic\Synthesizers;

use ReflectionClass;
use ReflectionObject;

class AnonymousSynth extends ObjectSynth
{
    public static $key = 'anms';

    static function registerAnonymousCacheClassAutoloader() {
        spl_autoload_register(function ($class) {
            if (str_starts_with($class, 'SyntheticCache')) {
                include_once storage_path('framework/cache/synthetic-'.sha1($class).'.php');

                return true;
            }

            return false;
        });
    }

    static function match($target) {
        return static::isAnonymousClass($target) || static::isAnonymousCacheClass($target);
    }

    static function isAnonymousClass($target) {
        return is_object($target)
        && class_exists(get_class($target))
        && (new ReflectionClass($target))->isAnonymous();
    }

    static function isAnonymousCacheClass($target) {
        return is_object($target)
            && class_exists(get_class($target))
            && str(get_class($target))->startsWith('SyntheticCache');
    }

    function dehydrate($target, $context) {
        if ($this->isAnonymousClass($target)) {
            $cacheClass = $this->getAnonymousCacheClass($target);

            $target = new $cacheClass;
        }

        return parent::dehydrate($target, $context);
    }

    function getAnonymousCacheClass($target) {
        [$path, $full, $namespace, $class] = $this->getStuff($target);

        $this->ensureCacheFileExists($path, $target);

        return $full;
    }

    function ensureCacheFileExists($path, $target)
    {
        $originalFile = (new ReflectionClass($target))->getFileName();

        if (! is_file($path) || (filemtime($path) < filemtime($originalFile))) {
            file_put_contents($path, $this->generateClass($target));
        }
    }

    function generateClass($target)
    {
        [$path, $full, $namespace, $class] = $this->getStuff($target);

        $raw = file((new ReflectionObject($target))->getFilename());
        $start = (new ReflectionObject($target))->getStartLine();
        $end = (new ReflectionObject($target))->getEndLine();

        $firstLine = $raw[$start - 1];
        $suffix = (string) str($firstLine)->between('new class ', '{');
        if (! str($suffix)->contains('extends')) {
            $suffix = 'extends \\Synthetic\\Component' . $suffix;
        }

        $uses = '';

        foreach ($raw as $line) {
            if (str_starts_with($line, 'use ')) {
                $uses .= $line;
            }
        }

        $body = '';
        foreach (range($start, $end - 2) as $line) {
            $body .= $raw[$line];
        }

        return <<<PHP
<?php

namespace $namespace;

$uses

class $class $suffix {
$body
}
PHP;
    }

    function getStuff($target) {
        $hash = md5(json_encode([
            (new ReflectionObject($target))->getFilename(),
            (new ReflectionObject($target))->getStartLine(),
        ]));

        $namespace = 'SyntheticCache';

        $class = (string) str('a'.$hash)->studly();

        $full = $namespace . '\\' . $class;

        $path = storage_path('framework/cache/synthetic-'.sha1($full).'.php');

        return [$path, $full, $namespace, $class];
    }
}
