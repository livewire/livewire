# Writing Documentation

- Handler methods use the `handle` prefix (e.g. `handleSort`, not `sort` or `sortItem`)
- Always include `wire:key` in `@foreach` loop examples
- Never expose Eloquent collections as public properties — use `#[Computed]` with relationship scoping instead
- Match domain model complexity to feature complexity: simple domain (TodoList -> tasks) for basic examples, richer domain (Board -> Column -> Card) for advanced/group features
- Scope model lookups through relationships (`$this->board->cards()->findOrFail($id)`) — never use global unscoped `Model::findOrFail($id)`
