<?php

namespace Livewire\Features\SupportReactiveProps;

use function Livewire\on;
use function Livewire\after;
use function Livewire\store;
use Livewire\ComponentHook;

class SupportReactiveProps extends ComponentHook
{
    public static $pendingChildParams = [];

    static function provide()
    {
        on('flush-state', fn() => static::$pendingChildParams = []);

        on('mount.stub', function ($tag, $id, $params, $parent, $key) {
            static::$pendingChildParams[$id] = $params;
        });

        on('hydrate', function ($component, $memo) {
            $pooled = store()->get('pooledChildIds', []);

            foreach ($memo['children'] ?? [] as [$tag, $childId]) {
                $pooled[$childId] = true;
            }

            store()->set('pooledChildIds', $pooled);
        });

        after('hydrate', function ($component, $memo) {
            if (empty($memo['props'] ?? [])) return;

            if (store($component)->get('reactivePropsChanged', false)) return;

            $pooled = store()->get('pooledChildIds', []);

            if (! isset($pooled[$component->getId()])) return;

            $component->skipRender();
        });
    }

    static function hasPassedInProps($id) {
        return isset(static::$pendingChildParams[$id]);
    }

    static function getPassedInProp($id, $name) {
        $params = static::$pendingChildParams[$id] ?? [];

        return $params[$name] ?? null;
    }
}
