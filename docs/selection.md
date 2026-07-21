Livewire provides a dedicated `Selection` object for building selection interfaces like checkboxes in table rows or items in a media library.

A selection tracks checked keys across renders and pagination, powers "select page" and "select all" controls, and constrains bulk-action queries to exactly the selected rows. All the tricky parts, including true "select all" across an entire result set, are taken care of for you.

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

    public Selection $selected;

    #[Computed]
    public function invoices()
    {
        return auth()->user()->invoices()->latest()->paginate(10);
    }

    public function archiveSelected()
    {
        auth()->user()->invoices()
            ->whereSelected($this->selected)
            ->update(['archived_at' => now()]);

        $this->selected->clear();
    }
};
```

```blade
<div>
    <button wire:click="archiveSelected">Archive selected</button>

    @foreach ($this->invoices as $invoice)
        <label wire:key="{{ $invoice->id }}">
            <input type="checkbox" wire:model="selected" value="{{ $invoice->id }}">

            {{ $invoice->number }}
        </label>
    @endforeach

    {{ $this->invoices->links() }}
</div>
```

A few things are happening here:

- Livewire initializes typed `Selection` properties automatically, so you don't need to assign one in `mount()`. If you want rows pre-selected, you can assign one yourself: `$this->selected = new Selection([1, 2]);`
- A checkbox with a `value` attribute bound using `wire:model="selected"` models membership: checking it adds its value to the selection, unchecking removes it
- Checkbox changes are tracked client-side and sync with the next request, so checking boxes doesn't trigger network requests on its own
- Because the selection is component state, it survives pagination: keys checked on page one are still selected after navigating to page two

## Using the selection in the template

Selection methods can be called directly inside directive expressions. These run immediately on the client without a network round trip, and the updated selection syncs to the server with the next request:

```blade
<div>
    <span wire:text="selected.count() + ' selected'"></span>

    <button wire:click="selected.clear()">Clear</button>
</div>
```

Combined with [wire:if](/docs/4.x/wire-if), this makes a selection toolbar that appears the moment the first row is checked:

```blade
<template wire:if="selected.any()">
    <div>
        <span wire:text="selected.count() + ' selected'"></span>

        <button wire:click="selected.clear()">Clear</button>

        <button wire:click="archiveSelected">Archive selected</button>
    </div>
</template>
```

## Selecting the current page

Tables commonly offer a checkbox in the header that selects every visible row. Bind it to `selected.page`:

```blade
<input type="checkbox" wire:model="selected.page">
```

This checkbox is fully wired up automatically:

- It's checked when every rendered row checkbox is selected
- It shows the native indeterminate state when only some are
- Toggling it selects or deselects every rendered row, leaving keys selected on other pages untouched

"The page" means whatever checkboxes are currently rendered in the browser. Because that's a client-side notion, the page methods `selectPage()`, `deselectPage()`, and `isPageSelected()` are available in directive expressions and JavaScript rather than on the PHP object:

```blade
<button wire:click="selected.selectPage()">Select page</button>
```

## Selecting all results

Selecting the current page is often just the first step. When a user wants to select all 2,500 matching records, enumerating every key in the browser would be impractical. Instead, `selectAll()` flips the selection into *select-all* mode: the selection now represents every result, and tracks only the *exceptions* (rows that have been unchecked since).

```blade
<button wire:click="selected.selectAll()">
    Select all {{ $this->invoices->total() }}
</button>
```

Everything keeps working in select-all mode. Bound checkboxes render checked, unchecking a row records an exception, the header checkbox and `contains()` behave exactly as you'd expect, and `whereSelected()` automatically constrains queries with `whereNotIn` instead of `whereIn`.

The differences only show up when you enumerate the selection:

- `keys()` throws, since the selected keys can't be listed without the full result set
- `except()` returns the unchecked keys
- `isAll()` reports whether the selection is in select-all mode
- `isAllSelected()` reports select-all mode with no exceptions, which is useful for hiding the "Select all" button once it's done its job:

```blade
<template wire:if="! selected.isAllSelected()">
    <button wire:click="selected.selectAll()">
        Select all {{ $this->invoices->total() }}
    </button>
</template>
```

### Counting a select-all selection

In select-all mode, the selection can't know its own count without knowing how many results exist. You can feed it a total from your paginator using `setTotal()`:

```php
#[Computed]
public function invoices()
{
    return tap(auth()->user()->invoices()->latest()->paginate(10), function ($paginator) {
        $this->selected->setTotal($paginator);
    });
}
```

With a total on hand, `count()` works in both modes, everywhere. While in select-all mode, it returns the total minus any exceptions:

```blade
<span wire:text="selected.count() + ' selected'"></span>
```

`setTotal()` also accepts a plain integer, and you can pass a total to `count()` directly: `$this->selected->count($total)`. The stored total is readable anywhere via `total()`, which returns `null` if one was never set.

Without a total, a select-all count is unknowable: `count()` throws in PHP and returns `null` in JavaScript. If you'd rather not track one, you can branch on the mode instead:

```blade
<span wire:text="selected.isAll() ? 'All selected' : selected.count() + ' selected'"></span>
```

## Beyond checkboxes

Nothing about a selection is specific to checkboxes, or to table rows. It's a set of tracked keys, so it fits any interface where users mark items: favoriting products, expanding rows to reveal details, or adding items to a compare list.

Here, clicking an invoice toggles its detail panel. There are no checkboxes involved, just `toggle()` and `contains()`:

```php
public Selection $expanded;
```

```blade
@foreach ($this->invoices as $invoice)
    <div wire:key="{{ $invoice->id }}">
        <button wire:click="expanded.toggle({{ $invoice->id }})">
            {{ $invoice->number }}
        </button>

        <template wire:if="expanded.contains({{ $invoice->id }})">
            <p>{{ $invoice->description }}</p>
        </template>
    </div>
@endforeach
```

The panels open instantly since both methods run client-side, and the expanded keys sync to the server and survive pagination like any other selection.

## Security

Treat every selection as user input. The keys, and the select-all mode itself, arrive from the browser like any other `wire:model` value, so a hostile client can submit a payload claiming any keys are selected, or that everything is.

The rule: only apply a selection to a query that is already scoped to the records the current user owns.

```php
// Safe: scoped through the owner relationship...
auth()->user()->invoices()->whereSelected($this->selected)->delete();

// Unsafe: a forged payload can target any row in the table...
Invoice::whereSelected($this->selected)->delete();
```

With an ownership-scoped query, a forged selection can never reach more rows than the user could select by clicking every checkbox themselves.

The scoping has to be by ownership specifically. A filter that narrows the query by something else does not protect the query's boundary:

```php
// Unsafe: "paid" narrows the results, but not to the current user's records.
// A forged select-all selection resolves to "every paid invoice, minus none" —
// across every user...
Invoice::where('status', 'paid')->whereSelected($this->selected)->delete();
```

As a backstop, `whereSelected()` refuses to apply a select-all selection to a completely unconstrained query (no where clauses, no global scopes), since that combination would let a forged payload target the entire table. This is only a last line of defense against the most catastrophic case: it can see that a query is constrained, but not whether the constraint scopes to the current user. Scoping through the owner relationship remains your responsibility.

If a table-wide query is genuinely what you want, in an admin panel for example, you can acknowledge it explicitly:

```php
Invoice::whereSelected($this->selected, unscoped: true)->update(['archived_at' => now()]);
```

## Testing

You can set a selection like any other property, using a plain array of keys, and assert on the observable outcome:

```php
it('archives selected invoices', function () {
    $user = User::factory()->has(Invoice::factory()->count(3))->create();

    [$first, $second, $third] = $user->invoices;

    Livewire::actingAs($user)
        ->test('invoices.index')
        ->set('selected', [$first->id, $second->id])
        ->call('archiveSelected');

    expect($user->invoices()->whereNotNull('archived_at')->count())->toBe(2);
});
```

## JavaScript

`$wire.selected` is the same object the checkboxes are bound to, with the full method set available from component scripts:

```blade
<script>
    $wire.selected.select(1)

    $wire.selected.contains(1) // true
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
| `count($total = null)` | Number of selected keys. In select-all mode a total is required: without one it throws in PHP and returns `null` in JavaScript |
| `any()` | Whether anything is selected |
| `isEmpty()` | Whether nothing is selected |
| `keys()` | The selected keys. Throws in select-all mode |
| `selectAll()` | Enter select-all mode: every result, minus exceptions |
| `isAll()` | Whether the selection is in select-all mode |
| `isAllSelected()` | Whether in select-all mode with no exceptions |
| `except()` | The exception keys while in select-all mode |
| `total()` | The stored total, or `null` if one was never set |
| `clear()` | Deselect everything and leave select-all mode |
| `reset()` | Alias of `clear()`, matching the `reset()` on form objects |

Keys are compared loosely, since checkbox values arrive as strings while database keys are often integers, so `'1'` and `1` refer to the same row.

### PHP-only methods

| Method | Description |
|--------|-------------|
| `setTotal($total)` | Feed a result total for select-all counts. Accepts a paginator or an integer |

### Template and JavaScript-only methods

The current page is defined by which checkboxes are rendered in the browser, so these have no PHP counterpart:

| Method | Description |
|--------|-------------|
| `selectPage()` | Select every rendered checkbox's value |
| `deselectPage()` | Deselect every rendered checkbox's value |
| `isPageSelected()` | Whether every rendered checkbox is selected |

### Bindings

| Binding | Description |
|---------|-------------|
| `wire:model="selected"` | On a checkbox with a `value` attribute. Models that value's membership in the selection |
| `wire:model="selected.page"` | On a header checkbox. Models whole-page selection, with automatic indeterminate state |

### whereSelected

An Eloquent builder macro that constrains a query to the selection: `whereIn` in normal mode, `whereNotIn` in select-all mode:

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
- Default: `false`, meaning an unconstrained select-all query throws

## See also

- **[Pagination](/docs/4.x/pagination)** — Selections persist as users navigate between pages of results
- **[wire:model](/docs/4.x/wire-model)** — Understand the data binding that selection checkboxes build on
- **[Properties](/docs/4.x/properties)** — How component properties are hydrated and synced
- **[Security](/docs/4.x/security)** — The hostile-client model behind the scoped-query rule
