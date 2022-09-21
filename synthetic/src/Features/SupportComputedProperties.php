<?php

namespace Synthetic\Features;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Synthetic\Synthesizers\ObjectSynth;

class SupportComputedProperties
{
    public function __invoke()
    {
        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof ObjectSynth) return;

            [$computedProperties, $deps] = $this->getComputedProperties($target);

            return function (&$properties) use ($computedProperties, $deps, $context) {
                $context->addMeta('deps', $deps);

                foreach ($computedProperties as $key => $value) {
                    $properties[$key] = $value;
                }

                return $properties;
            };
        });

        app('synthetic')->on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof ObjectSynth) return;

            $deps = $meta['deps'];

            return function ($target) use ($rawValue, $deps) {
                foreach ($rawValue as $key => $value) {
                    if ($this->isComputedProperty($target, $key)) {
                        $this->storeComputedProperty($target, $key, $value, $deps[$key]);
                        continue;
                    }
                }

                $this->hashComputedPropertyDeps($target, $deps);

                return $target;
            };
        });
    }


    function isComputedProperty($target, $key)
    {
        return !! collect($this->getComputedMethods($target))
            ->first(function ($i) use ($key) {
                [$method, $property] = $i;

                return $property === $key;
            });
    }

    function storeComputedProperty($target, $key, $value, $deps)
    {
        if (! isset($target->__computeds)) $target->__computeds = [];

        $target->__computeds[$key] = ['value' => $value, 'deps' => $deps];
    }

    function hashComputedPropertyDeps($target, $deps)
    {
        if (! isset($target->__computedHashes)) $target->__computedHashes = [];

        $target->__computedHashes = collect($deps)
            ->flatten()
            ->unique()
            ->mapWithKeys(function ($property) use ($target) {
                return [$property => md5(json_encode($target->$property))];
            })
            ->toArray();
    }

    function computedPropertyDepHasChanged($target, $key) {
        if (! isset($target->__computedHashes)) return true;

        if (md5(json_encode($target->$key)) !== $target->__computedHashes[$key] ?? '') return true;

        return false;
    }

    function getComputedProperties($target) {
        $computedDeps = [];
        $properties = [];

        foreach ($this->getComputedMethods($target) as [$methodName, $propertyName]) {
            if (isset($target->__computeds[$propertyName])) {
                $deps = $target->__computeds[$propertyName]['deps'];

                $rehash = false;
                foreach ($deps as $dep) {
                    if ($this->computedPropertyDepHasChanged($target, $dep)) {
                        $rehash = true;
                        break;
                    }
                }

                if (! $rehash) {
                    $computedDeps[$propertyName] = $target->__computeds[$propertyName]['deps'];
                    $properties[$propertyName] = $target->__computeds[$propertyName]['value'];

                    continue;
                }
            }

            $deps = [];
            $result = $this->trackDeps($target, $methodName, $deps);

            $computedDeps[$propertyName] = $deps;
            $properties[$propertyName] = $result;
        }

        return [$properties, $computedDeps];
    }

    function getComputedMethods($target)
    {
        $methods = (new ReflectionClass($target))->getMethods(ReflectionMethod::IS_PUBLIC);

        return collect($methods)
            ->map(function ($method) {
                if ($method->getDocComment() && str($method->getDocComment())->contains('@computed')) {
                    return [$method->getName(), $method->getName()];
                }

                if (str($method->getName())->startsWith('computed')) {
                    return [$method->getName(), str($method->getName())->after('computed')->camel()->__toString()];
                }

                return false;
            })
            ->filter()
            ->toArray();
    }

    function trackDeps($original, $methodName, &$deps)
    {
        $class = get_class($original);

        $trap = eval(<<<EOT
        return new class extends $class {
            public \$__deps = [];
            public \$__original = [];

            public function __takeover(\$original) {
                \$this->__deps = [];
                \$this->__original = \$original;
                \$properties = (new ReflectionClass(\$this))->getProperties(ReflectionProperty::IS_PUBLIC);

                foreach (\$properties as \$property) {
                    if (in_array(\$property->getName(), ['__deps', '__original'])) continue;
                    unset(\$this->{\$property->getName()});
                }
            }

            public function __get(\$prop) {
                \$this->__deps[] = \$prop;
                return \$this->__original->{\$prop};
            }

            public function __set(\$prop, \$value) {
                \$this->__deps[] = \$prop;
                \$this->__original->{\$prop} = \$value;
            }
        };
        EOT);

        $methods = (new ReflectionClass($original))->getMethods(ReflectionProperty::IS_PUBLIC);
        foreach ($methods as $m) {
            if ($m->getName() === $methodName) {
                $method = $m->getClosure($original);
            }
        }

        $trappedMethod = $method->bindTo($trap);

        $trap->__takeover($original);

        $result = $trappedMethod();

        foreach ($trap->__deps as $dep) $deps[] = $dep;

        return $result;
    }
}
