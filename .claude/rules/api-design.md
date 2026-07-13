# Designing New Livewire APIs

Philosophy, taste, and decision-making for creating new public APIs (directives, attributes, methods, modifiers). Every rule here is reverse-engineered from a shipped v4 decision — when in doubt, find the closest precedent and match it. For file structure and registration mechanics, see `adding-features.md`; this doc covers the judgment layer that comes before it.

## The worldview

You are extending a language, not adding to a library. Livewire's promise is building dynamic UIs without leaving HTML/Blade/PHP — an API succeeds when it disappears into its host language. Livewire speaks three dialects, each with a treaty:

- **Template (Blade/HTML):** the `wire:` prefix is a namespace treaty with HTML. Everything inside it must read like a native HTML attribute. `@directives` must read like Blade.
- **Class (PHP):** `#[Attributes]` are declarative facts about a property/method/class. Methods on `$this` must read like Laravel — named args, fluent, expressive.
- **Script (JS):** the `$` prefix marks framework magic (`$wire.$set()`, `$refresh`), leaving the unprefixed namespace for the *user's* properties and methods. `__` prefix (`__dispatch`, `__lazyLoad`) marks internal wire-format methods, never public API.

**The fluency test:** could someone who knows the rest of Livewire — but has never seen this feature — guess the API on the first try? Users guess `wire:keydown.enter` exists before reading docs. That's the bar. If the guessable spelling and your spelling differ, prefer the guessable one.

**The final test is silence:** the best APIs generate no clarifying questions. If a design review is full of "wait, what does X do?", the design isn't done — the answers belong in the names.

## The ladder: the best new API is no new API

Work down in order. Stop at the highest rung that solves the problem. Every rung descended adds vocabulary users must learn and we must maintain forever.

**Rung 0 — Ship behavior, not API.** Can the right thing simply happen, with no opt-in? Precedent: `data-loading` auto-applied to request-triggering elements (stylable with plain Tailwind, no directive), chunked/resumable uploads with zero markup change, smart `wire:key` defaults, `wire:submit` auto-disabling inputs in flight.

**Rung 1 — Inherit an existing vocabulary.** Does the browser, Alpine, or Laravel already have a word and semantic for this? Delegate and keep their exact name — you inherit the user's existing knowledge and the platform's future. Precedent: the wildcard directive (any unrecognized `wire:event` → Alpine `x-on:event`) inherited Alpine's entire event/modifier system in one move; `wire:intersect` mirrors `x-intersect`; `#[Validate]` takes Laravel rules verbatim; v4 `wire:transition` *deleted* its custom modifiers to hand the job to native View Transitions.

**Rung 2 — Extend an existing word.** Is this a variation of something that exists? Reach for a modifier (`.async`), a colon sub-directive (`wire:sort:item`), an attribute parameter (`#[Lazy(bundle: true)]`), or a named argument (`stream(replace: true)`) before minting. Precedent: `.renderless` and `.preserve-scroll` extended every action directive at once; `defer` arrived as a sibling of `lazy` sharing the placeholder machinery and `bundle` option.

**Rung 3 — Mint a new word.** Only for a genuinely new capability. A minted word carries full cost: directive/attribute + docs + tests + JS/PHP registration + a permanent slot in every user's head. Precedent: `wire:sort`, `@island`, `wire:stream`, `#[Computed]`.

Notes: Rung 1 is a superpower, not a compromise — delegation gets you the platform's future for free. And rung 0 sometimes arrives years later (`wire:loading` eventually earned the zero-API `data-loading` sibling once Tailwind variants made it stylable) — revisit old features when the platform moves; taste includes subtraction.

## Placement: who decides, and when?

If you reach rung 3, match the surface to whoever makes the call:

| Surface | Who decides | When | Precedent |
|---|---|---|---|
| `wire:` directive | Template author | Per element, at markup-write time | `wire:model`, `wire:poll`, `wire:confirm` |
| Modifier | Template author | Tuning *how/when* an existing directive acts | `.live`, `.debounce.500ms`, `.async` |
| Tag prop | Parent template | Per component instance | `<livewire:revenue lazy />`, `:$post`, `@saved="..."` |
| Property/method attribute | Component author | A declarative *fact* about one property/method | `#[Locked]`, `#[Computed]`, `#[On]`, `#[Url]` |
| Class attribute | Component author | A fact about the whole component | `#[Lazy]`, `#[Layout]`, `#[Isolate]` |
| Method on `$this` | Component author | At runtime, conditionally, mid-action | `$this->dispatch()`, `->stream()`, `->skipRender()` |
| Lifecycle hook method | Component author | Reacting to a framework moment | `mount()`, `updatedFoo()`, `placeholder()` |
| `$wire` magic | Script author | Client-side, per component | `$wire.$watch()`, `$wire.$js.foo = fn`, `$wire.$errors` |
| Global `Livewire.*` | Script author | Client-side, app-wide | `Livewire.on()`, `.interceptRequest()`, `.directive()` |
| Config / facade | App owner | Once, app-wide — the last resort | `smart_wire_keys`, `Livewire::setUpdateRoute()` |
| Hooks / synths / interceptors | Package & framework extenders | Extending Livewire itself | `ComponentHook`, `Synth`, `interceptMessage()` |

**Attribute vs. method is grammatical:** an attribute states a *fact* ("this property may never be tampered with" → `#[Locked]`); a method performs an *act* ("redirect now, because validation passed" → `$this->redirect()`). When something is both — renderless-ness is a fact about a method AND a per-call template choice — ship both doors with one name: `#[Renderless]` + `wire:click.renderless`.

### Decision tree

1. Can it just be default behavior, no opt-in? → **Rung 0.** Make it automatic; expose state as a plain data-attribute if styling needs a hook.
2. Does the browser/Alpine/Laravel already have a word for it? → **Rung 1.** Delegate, keep their exact vocabulary.
3. Is it a new *tuning* of an existing feature (how/when, not what)? → **Rung 2.** Dot-modifier + matching attribute/param if it's also a per-method fact.
4. Is it a new *role* inside an existing feature (different job, same family)? → **Rung 2.** Colon sub-directive (`wire:sort:item`, `wire:navigate:scroll`, `wire:for:key`).
5. Otherwise **mint (rung 3)**, placed by decision-maker:
   - Template author, per element → new `wire:` directive
   - Component author declaring a fact → new `#[Attribute]`
   - Component author acting at runtime → new method via `Handles*` trait
   - Script author → `$`-prefixed `$wire` property or `Livewire.*` global
   - App owner, once → config key (only if no better default exists)
   - Extender of Livewire itself → hook/interceptor/synth, never a public component API

## Grammar & naming

**The single-word law.** Strive for single words everywhere: `wire:click.stop`, not `.stop-propagation`; `#[Locked]`, not `#[ImmutableFromClient]`. A compound name usually means you're describing the implementation instead of naming the concept. When a compound is genuinely necessary it must be self-evident (`.preserve-scroll` earns its hyphen).

**Unambiguity beats brevity — the only thing that does.** `wire:sort:id` → `wire:sort:group-id` ("id" read as the item's id); `#[Rule]` → `#[Validate]` (collided with Laravel `Rule` objects); `stream(to:)` → `stream(el:)` ("to" didn't say what kind of destination). If a name could be confused with something else, it's wrong.

**Directives are verbs or observable states; modifiers are adverbs.** A directive names *what happens* (`wire:click`, `wire:navigate`, `wire:stream`) or *what is reflected* (`wire:loading`, `wire:dirty`, `wire:offline`). Modifiers only tune how/when/how much:

- A modifier never changes the noun. If `wire:x.foo` does a fundamentally different thing than `wire:x`, promote it to a colon sub-directive or its own word.
- Parameterized modifiers put the value in the next dot segment with units attached: `.debounce.500ms`, `.poll.2s`, `.margin.200px`, `.threshold.50`. Never `.debounce(500)`.
- Chains compose left-to-right and read as a sentence: `wire:model.live.debounce.250ms`.
- Livewire modifiers must not collide with Alpine's. Pass-through modifiers (`.prevent`, `.stop`, `.self`, `.window`) keep Alpine's exact meaning; Livewire-only modifiers (`.async`, `.renderless`) are stripped before delegation. Never create a modifier whose meaning differs by context.

**Colons namespace roles; dots tune behavior.** When a feature has multiple *participants*, each gets a colon-scoped role under the parent word: `wire:sort` + `wire:sort:item` / `:group` / `:group-id` / `:handle` / `:ignore`. Scroll preservation across navigations is `wire:navigate:scroll`, not a free-floating `wire:scroll` — roles belong to their owning feature. Corollary: bare top-level names are expensive real estate; before claiming one, ask which existing feature would want to own it as a colon role.

**Attributes have parts of speech:**
- States are adjectives/participles: `#[Locked]`, `#[Reactive]`, `#[Lazy]`, `#[Renderless]`, `#[Isolate]`, `#[Async]`
- Bindings are nouns naming the destination or identity: `#[Url]`, `#[Session]`, `#[Layout]`, `#[Title]`, `#[Computed]`
- Behaviors are verbs: `#[Validate]`, `#[Authorize]`; `#[On('event')]` reads as the preposition it is
- Options go in constructor params with sensible defaults (`#[Url(as: 'q', history: true)]`), never in sibling attributes

**Vocabulary sources, in priority order:** 1) the web platform (`dispatch` beat `emit` because `CustomEvent` made "dispatch" the browser's word), 2) Alpine (`wire:text`/`wire:show`/`wire:cloak` mirror `x-*`), 3) Laravel (validation, authorize, pagination), 4) the broader ecosystem (islands, slots, refs), 5) invention — last. Every borrowed word is documentation you don't have to write.

## Defaults are the API

Most users never pass an option; the default *is* the feature for 90% of them.

1. **Users opt into cost, never into correctness.** `wire:model` defers network by default; `.live` *buys* per-keystroke requests. Islands render eagerly unless marked `lazy`. The expensive path always leaves a visible marker in the code that chose it. (v3's ".defer everywhere" era is the cautionary tale that made v4 flip the default.)
2. **The default should match what the element already means.** `wire:submit` auto-`preventDefault()`s — nobody wire-submits wanting a page reload. v4 `wire:model` ignores bubbled child events by default (old behavior became `.deep`) because binding a container was a trap. Ask what the user would assume happens; make that happen.
3. **Config is the last resort, and every key needs a victim.** A config option is a decision you failed to make. Each shipped key has a concrete constituency (`csp_safe` → locked-down enterprises, `pagination_theme` → Bootstrap shops). If you can't name who changes a key and why, delete it and pick the better default. A modifier or param — a decision made near the code it affects — beats a config key every time.

**Progressive disclosure is the shape of every good feature:** zero-config default → modifier/param → attribute option → method override → hook/interceptor → synthesizer. Loading states walk this exact ladder: `data-loading` free → `wire:loading` → `wire:target` → `interceptMessage()`.

## One feature, many doors, one name

A capability usually deserves an entrance from each dialect. Doors differ in grammar, never in vocabulary:

| Capability | Template | Declarative PHP | Imperative PHP | JS |
|---|---|---|---|---|
| Events | `@saved="close"` on tag | `#[On('saved')]` | `$this->dispatch('saved')` | `$wire.$dispatch()` / `Livewire.on()` |
| Skip re-render | `wire:click.renderless` | `#[Renderless]` | `$this->skipRender()` | — |
| Parallel actions | `wire:click.async` | `#[Async]` | — | — |
| Lazy/deferred load | `<livewire:x lazy />`, `@island(lazy: true)` | `#[Lazy]` / `#[Defer]` | `placeholder()` | — |
| Transitions | `wire:transition` | `#[Transition]` | `$this->transition()` / `skipTransition()` | — |
| Client-side actions | `wire:click="$js.bookmark"` | `#[Js]` | `$this->js('...')` | `$wire.$js.bookmark = fn` |
| Validation state | `@error('email')` | `#[Validate('required')]` | `$this->validate()` / `addError()` | `$wire.$errors.first('email')` |
| Islands | `@island(...)`, `wire:island.append` | — | — | `$wire.$island('feed', ...)` |

Rules from the table:
- **The name survives translation.** *Renderless* is `.renderless` and `#[Renderless]`; *lazy* is a prop, an attribute, and an island argument. If you want different names per door, you have the wrong name.
- **Not every cell must be filled.** `#[Async]` has no imperative form because "become async mid-execution" is incoherent. Fill a cell only when that dialect's author genuinely makes that decision. Empty cells are taste; forced symmetry is bloat.
- **PHP and JS mirror each other** where both sides genuinely act: `$this->dispatch()` ↔ `$wire.$dispatch()`. The extension layers rhyme too — `ComponentHook` and PHP attributes share one lifecycle vocabulary (`mount/hydrate/update/call/render/dehydrate`); JS interceptors mirror it in `on`-prefixed callbacks (`onSend/onSuccess/onError/onFinish`).
- **The template door is usually primary.** Livewire users live in Blade. Design the template spelling first; the attribute/method forms are the "also available as" paragraph, not the reverse.

## State has a home

Every piece of state a feature introduces must be deliberately placed. The snapshot is a public, user-visible, wire-serialized contract — never a junk drawer.

| Home | What belongs there |
|---|---|
| Public property | State the user owns; client may read and write |
| `#[Locked]` property | Client may see but never write (IDs, invariants — models get this automatically) |
| Protected property | Server-only, re-derived each request; never serialized |
| `#[Computed]` | Derived data, especially queries — never expose an Eloquent collection as a public prop |
| `store($this)` | Feature machinery scoped to one request (error bags, dispatch queues, flags) |
| Memo (`$context->addMemo()`) | Feature state that survives round-trips but isn't user data (`lazyLoaded`, island metadata) |
| Effect (`$context->addEffect()`) | One-shot instructions to the client (dispatches, redirects, streamed islands), consumed by a matching `js/features/support*.js` |
| `#[Url]` / `#[Session]` | User state relocated to a longer-lived home the *user* chose |

Rule of thumb: **if the user didn't write the property, it doesn't belong on the component.** Rich values ride the wire through synthesizers (`Synth` with a terse key like `fil`, mirrored by `Livewire.synth()` in JS), not clever serialization inside your feature.

## Design for the hostile client

Every public method is an HTTP endpoint; every public property is user input. New APIs inherit this threat model on day one:

- Anything callable from the template is callable by an attacker — with any arguments, regardless of whether a button exists. New invocation paths (directive expressions, event listeners, magic actions) must flow through the same gate as ordinary actions; their parameters are request input.
- Safety should be automatic where possible (rung 0 applies to security): model IDs auto-lock, endpoints carry an `APP_KEY`-derived hash, snapshots are checksummed. Prefer making the dangerous thing impossible over documenting that it's dangerous.
- Where safety can't be automatic, make danger declarative and visible: `#[Locked]`, `#[Authorize]`, the wildcard directive's reserved-name denylist. An auditor should see a component's trust decisions in its attributes.
- New client→server channels need the full treatment: CSRF, component checksum, rate-limit posture (uploads ship throttled by default), and a story for what a forged payload can do. If the client sends an effect back, assume it lies.

## How APIs evolve: pave, rename, delete

- **Pave, don't break.** Better spellings arrive as the documented way while old ones keep working as aliases: `$wire.on` ↔ `$wire.$on`; legacy `commit`/`request` hooks are shims over interceptors; `wire:model.lazy` still means what it meant. Deprecation is a docs event first, a removal event much later.
- **Rename only when the name lies:** `emit` → `dispatch`, `#[Rule]` → `#[Validate]`, `stream(to:)` → `stream(el:)`, `wire:sort:id` → `wire:sort:group-id`. A rename that's merely "nicer" isn't worth the migration; one that fixes a lie always is.
- **Delete when the platform catches up.** v4 `wire:transition` dropped its entire modifier system to wrap native View Transitions — less surface, browser-quality behavior forever. These moments are rare chances to make the framework smaller; take them.
- **APIs drift toward the host language's most natural spelling.** `$wire.$js('bookmark', fn)` (registration call) became `$wire.$js.bookmark = fn` (plain assignment). If users would naturally *write* something different from your design, the natural spelling wins eventually — design it that way first.

## Smell tests

- **The name needs a sentence** → wrong altitude; climb the ladder (modifier? colon role? param?).
- **A modifier that changes the noun** → promote to colon sub-directive or its own word.
- **Config where a modifier would do** → push the decision to the code it affects.
- **Two spellings, two vocabularies** (`#[Parallel]` + `.async`) → one name through every door, or you have the wrong name.
- **Reimplementing an Alpine primitive** → wrap `Alpine.bind`/`effect`/`entangle`/a plugin, like every shipped directive.
- **Machinery in the snapshot** → `store($this)` / memos / effects.
- **Requires understanding internals** (morphing, hydration) to use correctly → redesign until the mental model is "HTML that stays in sync"; internals-shaped APIs belong in hooks/interceptors, labeled advanced.
- **The 10% case taxes the 90%** (required arg/wrapper/setup that only the rare case needs) → zero-config common case; the rare case pays with a modifier or param.
- **An option nobody will change** → delete it; options can be added later, never removed.
- **Forced symmetry** → fill a door only when that dialect's author genuinely makes that decision.

## Worked example: wire:sort (retrospective)

Drag-and-drop reordering, walked through the guide:

- **Ladder:** rung 0? No — needs a server handler and per-item identity. Rung 1? Partially — implementation delegates to Alpine's sort plugin, but the server round-trip is new. Rung 2? No existing directive owns reordering. Mint `wire:sort` — single word, matching the ecosystem's and Alpine's name for the concept.
- **Placement:** template author, per list element → a `wire:` directive whose expression names the handler, like every action directive: `wire:sort="handleSort"`.
- **Grammar:** multiple participants (container, items, optional handle, ignored regions, cross-list groups) = roles = colons: `wire:sort:item`, `:group`, `:handle`, `:ignore`.
- **Naming pressure-test:** cross-group moves need the source group's identity. First draft `wire:sort:id` was ambiguous (reads as the item's id) → shipped as `wire:sort:group-id`. Unambiguity beat the single-word law.
- **Defaults:** no handle attribute → whole item drags; no group → single-list sorting; handler receives `($id, $position)` — exactly the 90% case, with optional `$groupId` for the 10%.
- **Payoff of designing inside the system:** it rides the standard action pipeline, so `wire:loading`, `wire:target="handleSort"`, and interceptors work with it for free. A well-placed new word composes with every existing word at no extra cost.

## The checklist

Before shipping a new API, confirm:

1. **Climbed the ladder** — can't be default behavior, inherited vocabulary, or an extension of an existing word.
2. **Right surface for the decision-maker** — directive for template authors, attribute for declared facts, method for runtime acts, config only for app-wide policy with a named constituency.
3. **Passes the fluency test** — a fluent user could guess the spelling; single word unless ambiguity forces more; doesn't lie or collide with Laravel/Alpine/browser vocabulary.
4. **Grammar-correct** — modifiers tune how/when and never change the noun; roles use colons; parameterized modifiers carry units in the next segment; options are constructor params.
5. **One name through every door** — and doors only where that dialect's author genuinely decides.
6. **Defaults serve the 90%** — zero-config common case; users opt into cost, never into correctness; the element's native meaning is respected.
7. **State is homed** — nothing in the snapshot but user state; machinery in `store()`/memos/effects; rich types via synths.
8. **Hostile-client proof** — new invocation paths gated like actions; parameters treated as request input; safety automatic where possible, declarative where not.
9. **Composes for free** — rides the standard action/request pipeline so loading, targeting, dirty, polling, and interceptors just work.
10. **Anatomy complete** — feature folder with tests inside, two-file attribute split, four-point registration, docs page + nav entry, JS built on Alpine primitives (see `adding-features.md`).
