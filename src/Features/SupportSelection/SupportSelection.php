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
        Builder::macro('whereSelected', function (Selection $selection, ?string $column = null) {
            /** @var Builder $this */
            $column ??= $this->getModel()->getQualifiedKeyName();

            return $selection->isAll()
                ? $this->whereNotIn($column, $selection->except())
                : $this->whereIn($column, $selection->keys());
        });
    }
}
