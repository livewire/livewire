Checking rows in a table and acting on them — archive these, delete those, export the lot — is one of the most common patterns in data-driven interfaces. It's also surprisingly fiddly to build by hand: tracked keys scattered across an array property, "select all" state that lies after paginating, header checkboxes with three states, and bulk queries that better not trust the client.

Livewire provides a dedicated `Selection` object for this. It tracks checked keys across renders and pagination, powers "select page" and "select all" controls, and constrains bulk-action queries to exactly the selected rows.

## Basic usage

Add a typed `Selection` property to your component, bind each row's checkbox to it, and use `whereSelected()` to scope bulk actions:

```php
<?php // resources/views/components/invoices/⚡index.blade.php

use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\Component;
use Livewire\Selection;

new class extends Component {
    use WithPagination;

    public Selection $selection;

    #[Computed]
    public function invoices()
    {
        return auth()->user()->invoices()->latest()->paginate(10);
    }

    public function archiveSelected()
    {
        auth()->user()->invoices()
            ->whereSelected($this->selection)
            ->update(['archived_at' => now()]);

        $this->selection->clear();

        unset($this->invoices); // Bust the memoized list so this render reflects the update...
    }
};
```

```blade
<div>
    <button wire:click="archiveSelected">Archive selected</button>

    @foreach ($this->invoices as $invoice)
        <label wire:key="{{ $invoice->id }}">
            <input type="checkbox" wire:model="selection" value="{{ $invoice->id }}">

            {{ $invoice->number }}
        </label>
    @endforeach

    {{ $this->invoices->links() }}
</div>
```

A few things are happening here:

- Livewire initializes typed `Selection` properties automatically — no `mount()` assignment needed. To start with rows pre-selected, assign one yourself: `$this->selection = new Selection([1, 2]);`
- A checkbox with a `value` attribute bound via `wire:model="selection"` models membership: checking adds its value to the selection, unchecking removes it
- Checkbox changes are tracked client-side and sync with the next request — checking boxes doesn't trigger network requests on its own
- Because the selection is component state, it survives pagination: keys checked on page one are still selected after navigating to page two

## Using the selection in the template

Selection methods can be called directly inside directive expressions. These run immediately on the client — no network round trip — and the updated selection syncs to the server with the next request:

```blade
<div>
    <span wire:text="selection.count() + ' selected'"></span>

    <button wire:click="selection.clear()">Clear</button>
</div>
```

Combined with [wire:if](/docs/4.x/wire-if), this makes a selection toolbar that appears the moment the first row is checked:

```blade
<template wire:if="selection.any()">
    <div>
        <span wire:text="selection.count() + ' selected'"></span>

        <button wire:click="selection.clear()">Clear</button>

        <button wire:click="archiveSelected">Archive selected</button>
    </div>
</template>
```

## Selecting the current page

Tables commonly offer a checkbox in the header that selects every visible row. Bind it to the selection's `page` facet:

```blade
<input type="checkbox" wire:model="selection.page">
```

This checkbox is fully wired automatically:

- It's checked when every rendered row checkbox is selected
- It shows the native indeterminate state when only some are
- Toggling it selects or deselects every rendered row — keys selected on other pages are left untouched

"The page" means whatever row checkboxes are currently rendered in the browser. Because that's a client-side notion, the page methods — `selectPage()`, `deselectPage()`, and `isPageSelected()` — are available in directive expressions and JavaScript rather than on the PHP object:

```blade
<button wire:click="selection.selectPage()">Select page</button>
```

## Selecting all results

Selecting every row on the current page is often just the first step — the user really wants all 2,500 matching records. Enumerating every key client-side would be impractical, so `selectAll()` flips the selection into *select-all* mode: it now represents every result, and tracks only the *exceptions* — rows unchecked since.

```blade
<button wire:click="selection.selectAll()">
    Select all {{ $this->invoices->total() }}
</button>
```

Everything keeps working in select-all mode. Bound checkboxes render checked, unchecking a row records an exception, the page facet and `contains()` answer correctly, and `whereSelected()` constrains queries with `whereNotIn` instead of `whereIn` — automatically.

The differences only surface when you enumerate the selection:

- `keys()` throws — the selected keys can't be listed without the full result set
- `except()` returns the unchecked keys
- `isAll()` reports whether the selection is in select-all mode
- `isAllSelected()` reports select-all mode with no exceptions — useful for hiding the "Select all" button once it's done its job:

```blade
<template wire:if="! selection.isAllSelected()">
    <button wire:click="selection.selectAll()">
        Select all {{ $this->invoices->total() }}
    </button>
</template>
```

### Counting a select-all selection

In select-all mode, the selection can't know its own count — that requires knowing how many results exist. Feed it a total from your paginator with `setTotal()`:

```php
#[Computed]
public function invoices()
{
    return tap(auth()->user()->invoices()->latest()->paginate(10), function ($paginator) {
        $this->selection->setTotal($paginator);
    });
}
```

With a total on hand, `count()` works in both modes, everywhere — it returns the total minus any exceptions while in select-all mode:

```blade
<span wire:text="selection.count() + ' selected'"></span>
```

`setTotal()` also accepts a plain integer, and `count()` accepts one directly: `$this->selection->count($total)`.

Without a total, a select-all count is unknowable: `count()` throws in PHP and returns `null` in JavaScript. If you'd rather not track one, branch on the mode instead:

```blade
<span wire:text="selection.isAll() ? 'All selected' : selection.count() + ' selected'"></span>
```

## Security

Treat every selection as user input. The keys — and the select-all mode itself — arrive from the browser like any other `wire:model` value, so a hostile client can submit a payload claiming any keys, or claiming that *everything* is selected.

The rule: only apply a selection to a query that is already scoped to what the current user is allowed to touch.

```php
// Safe: scoped through the owner relationship...
auth()->user()->invoices()->whereSelected($this->selection)->delete();

// Unsafe: a forged payload can target any row in the table...
Invoice::whereSelected($this->selection)->delete();
```

With a scoped query, a forged selection can never reach more rows than the user could select by clicking every checkbox themselves.

As a backstop, `whereSelected()` refuses to apply a select-all selection to a completely unconstrained query — no where clauses, no global scopes — since that combination lets a forged payload target the entire table. If a table-wide query is genuinely what you want (an admin panel, for example), acknowledge it explicitly:

```php
Invoice::whereSelected($this->selection, unscoped: true)->update(['archived_at' => now()]);
```

## Testing

Set the selection like any other property — a plain array of keys — and assert on the observable outcome:

```php
it('archives selected invoices', function () {
    $user = User::factory()->has(Invoice::factory()->count(3))->create();

    [$first, $second, $third] = $user->invoices;

    Livewire::actingAs($user)
        ->test('invoices.index')
        ->set('selection', [$first->id, $second->id])
        ->call('archiveSelected');

    expect($user->invoices()->whereNotNull('archived_at')->count())->toBe(2);
});
```

## JavaScript

`$wire.selection` is the same object the checkboxes are bound to, with the full method set available from component scripts:

```blade
<script>
    $wire.selection.select(1)

    $wire.selection.contains(1) // true
</script>
```

## Reference

### Selection methods

Available on `Livewire\Selection` in PHP and on the bound property in directive expressions and JavaScript:

| Method | Description |
|--------|-------------|
| `select($key)` | Add a key to the selection |
| `deselect($key)` | Remove a key from the selection |
| `toggle($key)` | Select the key if unselected, deselect it otherwise |
| `contains($key)` | Whether the key is selected |
| `count($total = null)` | Number of selected keys — in select-all mode requires a total (throws in PHP, returns `null` in JavaScript without one) |
| `any()` | Whether anything is selected |
| `isEmpty()` | Whether nothing is selected |
| `keys()` | The selected keys — throws in select-all mode |
| `selectAll()` | Enter select-all mode: every result, minus exceptions |
| `isAll()` | Whether the selection is in select-all mode |
| `isAllSelected()` | Whether in select-all mode with no exceptions |
| `except()` | The exception keys while in select-all mode |
| `clear()` | Deselect everything and leave select-all mode |

Keys are compared loosely — checkbox values arrive as strings while database keys are often integers, so `'1'` and `1` refer to the same row.

### PHP-only methods

| Method | Description |
|--------|-------------|
| `setTotal($total)` | Feed a result total for select-all counts — accepts a paginator or an integer |
| `total()` | The stored total, or `null` |

### Template and JavaScript-only methods

The current page is defined by which checkboxes are rendered in the browser, so these have no PHP counterpart:

| Method | Description |
|--------|-------------|
| `selectPage()` | Select every rendered row checkbox's value |
| `deselectPage()` | Deselect every rendered row checkbox's value |
| `isPageSelected()` | Whether every rendered row checkbox is selected |

### Bindings

| Binding | Description |
|---------|-------------|
| `wire:model="selection"` | On a checkbox with a `value` attribute — models that value's membership in the selection |
| `wire:model="selection.page"` | On a header checkbox — models whole-page selection, with automatic indeterminate state |

### whereSelected

An Eloquent builder macro that constrains a query to the selection — `whereIn` in normal mode, `whereNotIn` in select-all mode:

```php
whereSelected(
    Selection $selection,
    ?string $column = null,
    bool $unscoped = false,
)
```

**`$selection`** (required)
- The selection to constrain the query by

**`$column`** (optional)
- The column to match keys against
- Default: the model's qualified primary key

**`$unscoped`** (optional)
- Acknowledge applying a select-all selection to an unconstrained query
- Default: `false` — an unconstrained select-all query throws

## See also

- **[Pagination](/docs/4.x/pagination)** — Selections persist as users navigate between pages of results
- **[wire:model](/docs/4.x/wire-model)** — Understand the data binding that selection checkboxes build on
- **[Properties](/docs/4.x/properties)** — How component properties are hydrated and synced
- **[Security](/docs/4.x/security)** — The hostile-client model behind the scoped-query rule
