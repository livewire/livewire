# Undocumented Features Found During v4 Upgrade Guide

These features exist in v4 but may need dedicated documentation pages or sections:

## High Priority

1. **`wire:intersect` directive**
   - Full API with all modifiers (`.once`, `.half`, `.full`, `.threshold.X`, `.margin.Xpx`)
   - Usage patterns and examples
   - Comparison with Alpine's `x-intersect`
   - Suggested location: New docs page or section in wire-* directives

2. **`data-loading` attribute**
   - Automatic addition to elements that trigger requests
   - Styling examples and use cases
   - Suggested location: Section in wire-loading.md or actions.md

3. **`wire:preserve-scroll` modifier**
   - How it works and when to use it
   - Examples of preventing layout jumps
   - Suggested location: Actions.md or new wire-preserve-scroll.md

4. **`$errors` magic property in JavaScript**
   - Full API (`.has()`, `.first()`, `.all()`, etc.)
   - Integration with Alpine
   - Suggested location: Forms.md or validation.md

5. **`$island()` magic action**
   - Calling island renders from templates
   - Passing modes and options
   - Suggested location: Islands.md (add a section)

6. **`$wire.$intercept()` magic**
   - Full API and callback structure
   - Use cases and examples
   - Suggested location: JavaScript.md (interceptors section)

7. **JavaScript modules in view-based components**
   - How `<script>` tags work without `@script` wrapper
   - Automatic `$wire` binding as `this`
   - Module caching and performance benefits
   - Suggested location: Components.md or JavaScript.md

8. **CSP-safe mode restrictions**
   - Complete list of what expressions are not allowed
   - Migration guide for existing apps
   - Workarounds and alternatives
   - Suggested location: New security.md section or dedicated csp.md page

## Medium Priority

9. **Slots and attribute forwarding**
   - Complete guide on using slots in components
   - `{{ $attributes }}` usage and examples
   - Suggested location: Components.md

10. **Smart wire:key automatic generation**
    - How it works behind the scenes
    - When you still need manual wire:key
    - Suggested location: Nesting.md or components.md

11. **Component namespaces**
    - How to use namespaced components
    - Examples: `<livewire:pages::dashboard />`
    - Suggested location: Components.md

12. **Partial morph hooks**
    - `Livewire.hook('partial.morph', ...)` and `partial.morphed`
    - When to use vs regular morph hooks
    - Suggested location: JavaScript.md (hooks section)

## Existing Docs to Update

- **lazy.md** - Already updated with defer and bundle features
- **actions.md** - Already updated with async actions
- **islands.md** - Already updated with renderIsland/streamIsland
- **components.md** - Already updated with convert command
- **wire-sort.md** - Already created and documented

## Notes

- Most of these features exist in the codebase but aren't mentioned in user-facing docs
- Some may be intentionally undocumented for now (beta features)
- Priority is based on likely user impact and common use cases
