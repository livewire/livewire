<?php

namespace Livewire\Features\SupportSelection;

use Illuminate\Database\Eloquent\Builder;
use Livewire\ComponentHook;

class SupportSelection extends ComponentHook
{
    public static function provide()
    {
        app('livewire')->propertySynthesizer(
            SelectionSynth::class
        );

        // Constrain a query to a selection — whereIn in include mode,
        // whereNotIn in except mode — so the mode branch can never be
        // written backwards by hand. Selection keys are client input:
        // apply this to an ownership-scoped query, never a global one...
        Builder::macro('whereSelected', function (Selection $selection, ?string $column = null, bool $unscoped = false) {
            /** @var Builder $this */
            $column ??= $this->getModel()->getQualifiedKeyName();

            // A select-all selection against a completely unconstrained
            // query lets a forged payload target every row in the table.
            // Require an existing constraint — or an explicit unscoped
            // acknowledgment that leaves a marker in code review...
            $isConstrained = count($this->getQuery()->wheres) > 0
                || count($this->getModel()->getGlobalScopes()) > 0;

            if ($selection->isAll() && ! $unscoped && ! $isConstrained) {
                throw new \RuntimeException(
                    'Livewire: Refusing to apply a select-all selection to an unscoped ['.get_class($this->getModel()).'] query — '.
                    'a forged payload could target every row in the table. Scope the query to the current user\'s own records '.
                    '(e.g. through an owner relationship) — a filter like where(\'status\', ...) narrows the query but does not '.
                    'bound it to the user, so reach for that, not unscoped: true. Only acknowledge with '.
                    'whereSelected($selection, unscoped: true) when a table-wide query is genuinely intended.'
                );
            }

            return $selection->isAll()
                ? $this->whereNotIn($column, $selection->except())
                : $this->whereIn($column, $selection->keys());
        });
    }
}
