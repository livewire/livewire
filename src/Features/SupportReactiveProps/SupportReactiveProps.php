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

        // Track which component IDs are children pooled alongside a parent commit.
        // This lets us distinguish "child committed because parent committed" from
        // "child committed independently (e.g. its own method call)."
        on('hydrate', function ($component, $memo) {
            $pooled = store()->get('pooledChildIds', []);

            foreach ($memo['children'] ?? [] as [$tag, $childId]) {
                $pooled[$childId] = true;
            }

            store()->set('pooledChildIds', $pooled);
        });

        // After all attributes (including BaseReactive) have hydrated, check whether
        // this reactive child actually needs to re-render. Skip the Blade render when:
        // no reactive prop changed, no client-side updates/calls, and the child was
        // only committed because it was pooled with a parent — not independently.
        after('hydrate', function ($component, $memo) {
            if (empty($memo['props'] ?? [])) return;

            if (store($component)->get('reactivePropsChanged', false)) return;

            if (! empty(store($component)->get('pendingUpdates', []))) return;
            if (! empty(store($component)->get('pendingCalls', []))) return;

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
