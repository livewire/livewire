# wire:model Modifier Layering PRD

## Problem

`wire:model` modifiers (`.blur`, `.change`, `.debounce`) only control network request timing. Ephemeral (client-side) state ALWAYS syncs immediately as user types. Users can't delay ephemeral sync until blur/enter/change.

## Feature

Split modifiers into two layers: modifiers BEFORE `.live` control ephemeral sync timing (forwarded to x-model), modifiers AFTER `.live` control network timing. Breaking change for v4.

## Before

```blade
wire:model                    {{-- ephemeral immediate, no network --}}
wire:model.blur               {{-- ephemeral immediate, network on blur --}}
wire:model.live               {{-- ephemeral immediate, network debounced --}}
wire:model.live.debounce.500ms {{-- ephemeral immediate, network debounced 500ms --}}
```

- All modifiers only affect network layer
- No way to control ephemeral sync timing
- `.blur`/`.change` don't affect when x-model syncs

## After

```blade
wire:model                    {{-- ephemeral immediate, no network --}}
wire:model.blur               {{-- ephemeral on blur, no network --}}
wire:model.change             {{-- ephemeral on change, no network --}}
wire:model.enter              {{-- ephemeral on enter, no network --}}
wire:model.blur.enter         {{-- ephemeral on blur OR enter, no network --}}
wire:model.live               {{-- ephemeral immediate, network debounced --}}
wire:model.live.blur          {{-- ephemeral immediate, network on blur (OLD .blur behavior) --}}
wire:model.blur.live          {{-- ephemeral on blur, network on blur --}}
wire:model.blur.live.debounce.500ms {{-- ephemeral on blur, network debounced 500ms after --}}
```

- Modifiers before `.live` → forwarded to x-model (controls ephemeral sync)
- Modifiers after `.live` → handled by Livewire (controls network)
- Everything chains sequentially: ephemeral syncs first, then network fires

## Relevant Files

- `js/directives/wire-model.js` — main implementation (lines 1-186)
- `node_modules/alpinejs/src/directives/x-model.js` — reference for how Alpine handles `.blur`/`.change`/`.enter` (lines 58-98)
- `src/Features/SupportDataBinding/BrowserTest.php` — existing browser tests

## Current Logic (wire-model.js:34-82)

```js
let isLive = modifiers.includes('live')
let isLazy = modifiers.includes('lazy') || modifiers.includes('change')
let onBlur = modifiers.includes('blur')
// ... these only control WHEN update() is called for network
// x-model always gets modifiers minus 'lazy','defer','debounce','throttle'
// meaning .blur/.change still get forwarded but DON'T affect ephemeral sync
// because wire:model uses get/set object pattern, not event listeners
```

Problem: `getModifierTail()` forwards `.blur` to x-model, but wire:model uses getter/setter pattern (`x-model.blur() { get(), set() }`), not Alpine's event-listener approach. So Alpine's `.blur` modifier has no effect.

## Changes Needed

1. **Parse modifiers into two groups** — split at `.live` boundary
   - `ephemeralModifiers` = everything before `.live`
   - `networkModifiers` = everything after `.live`

2. **Create extraction function** with clean interface:
   ```js
   let {
     ephemeralModifiers,  // forwarded to x-model tail
     onEphemeralSync,     // callback when ephemeral state changes
     shouldSendNetwork,   // fn to check if network should fire
   } = parseWireModelModifiers(modifiers)
   ```

3. **Forward ephemeral modifiers to x-model** — these get added to `x-model.blur.change.enter` etc.

4. **Add event listeners for network triggers** — if network modifiers include `.blur`, add `@blur` handler. Same for `.change`, `.enter`.

5. **Handle debounce/throttle at network layer** — wrap network callbacks in debounce/throttle based on network modifiers.

6. **x-model set() callback triggers network** — when x-model syncs (based on its modifiers), check if `.live` is present and call network update.

### Implementation Sketch

```js
directive('model', ({ el, directive, component, cleanup }) => {
    let { expression, modifiers } = directive

    // Split modifiers at .live boundary
    let liveIndex = modifiers.indexOf('live')
    let ephemeralModifiers = liveIndex === -1 ? modifiers : modifiers.slice(0, liveIndex)
    let networkModifiers = liveIndex === -1 ? [] : modifiers.slice(liveIndex + 1)
    let isLive = liveIndex !== -1

    // Build x-model modifier tail from ephemeral modifiers
    let xModelTail = buildModifierTail(ephemeralModifiers)

    // Build network update function with debounce/throttle
    let networkUpdate = buildNetworkUpdate(networkModifiers, component, expression, el, directive)

    // Determine network trigger events
    let networkTriggers = extractNetworkTriggers(networkModifiers)

    Alpine.bind(el, {
        ['@blur']() {
            if (networkTriggers.blur) networkUpdate()
        },
        ['@change']() {
            if (networkTriggers.change) networkUpdate()
        },
        ['@keydown.enter']() {
            if (networkTriggers.enter) networkUpdate()
        },
        ['x-model' + xModelTail]() {
            return {
                get() {
                    return dataGet(component.$wire, expression)
                },
                set(value) {
                    dataSet(component.$wire, expression, value)
                    // If live with no specific triggers, fire on every sync
                    if (isLive && networkTriggers.immediate) {
                        networkUpdate()
                    }
                },
            }
        }
    })
})
```

## Decisions

- **Breaking change in v4** — acceptable since v4 is ~2 weeks old
- **`.live` is the delimiter** — everything before controls ephemeral, after controls network
- **Modifiers compound** — `.blur.enter` means blur OR enter (both trigger sync)
- **Network inherits ephemeral timing by default** — if no network modifiers, network fires when ephemeral syncs
- **`.live` alone = debounced immediate** — current behavior, ephemeral immediate + network debounced
- **`.live.blur` = ephemeral immediate, network on blur** — preserves old `.blur` behavior for migration
- **Empty network modifiers with `.live`** — means "fire network when ephemeral syncs" (for `.blur.live`)

## Test File

`src/Features/SupportDataBinding/BrowserTest.php` (add to existing file)

### New Test Cases

Single consolidated test with multiple scenarios:

1. `wire:model` — ephemeral syncs on input, no network
2. `wire:model.blur` — ephemeral syncs on blur only, no network
3. `wire:model.change` — ephemeral syncs on change only, no network
4. `wire:model.enter` — ephemeral syncs on enter only, no network
5. `wire:model.blur.enter` — ephemeral syncs on blur OR enter, no network
6. `wire:model.live` — ephemeral immediate, network debounced
7. `wire:model.live.blur` — ephemeral immediate, network on blur (old behavior)
8. `wire:model.blur.live` — ephemeral on blur, network on blur
9. `wire:model.blur.live.debounce.500ms` — ephemeral on blur, network debounced
9. `wire:model.blur.live.throttle.500ms` — ephemeral on blur, network throttled

Each scenario: type into input, verify ephemeral state via `x-text="$wire.prop"`, verify server state via `{{ $prop }}`, trigger blur/enter/change, verify both states.

## Manual Testing

1. **Test ephemeral-only blur**
   - Create input with `wire:model.blur="title"`
   - Type "hello" — ephemeral should NOT update
   - Click away — ephemeral updates to "hello"
   - Server still shows old value

2. **Test network on blur (old behavior)**
   - Create input with `wire:model.live.blur="title"`
   - Type "hello" — ephemeral updates immediately
   - Click away — network request fires, server updates

3. **Test full blur.live**
   - Create input with `wire:model.blur.live="title"`
   - Type "hello" — ephemeral stays unchanged
   - Click away — ephemeral updates, network fires, server updates

## Documentation

**File:** `docs/wire-model.md`

**Section to modify:** "Customizing update timing" (line ~51) and "Reference" table (line ~291)

**Add:**
- Explain modifier layering concept
- Update examples to show new syntax
- Add migration note about breaking change
- Update modifier reference table

## Git

**Commit message:**
```
feat(wire:model): control ephemeral sync timing with modifiers

BREAKING: Modifiers before .live now control ephemeral (x-model) sync
timing, not just network timing. Previous .blur behavior is now .live.blur.

- wire:model.blur syncs ephemeral on blur (no network)
- wire:model.live.blur syncs ephemeral immediate, network on blur
- wire:model.blur.live syncs both on blur
- Supports .blur, .change, .enter and combinations

Migration: Replace wire:model.blur with wire:model.live.blur for old behavior.

Co-Authored-By: Claude Opus 4.5 <noreply@anthropic.com>
```

**PR Title:**
`feat(wire:model)!: control ephemeral sync timing with modifiers`

**PR Description:**
```
## Summary
- Modifiers before `.live` control ephemeral (x-model) sync timing
- Modifiers after `.live` control network timing
- BREAKING: `wire:model.blur` now delays ephemeral sync, use `.live.blur` for old behavior

## Test plan
- [ ] `wire:model.blur` delays ephemeral sync until blur
- [ ] `wire:model.live.blur` matches old `.blur` behavior
- [ ] `wire:model.blur.live` delays both ephemeral and network until blur
- [ ] Compound modifiers work (`.blur.enter`)
- [ ] Debounce/throttle work on network layer
```
