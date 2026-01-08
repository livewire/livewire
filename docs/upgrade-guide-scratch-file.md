im throwing up this doc but the structure and content isn't truth, just my exhuastive notes after reviewing the full diff of v3 to v4. Stick with the laravel way instead of my way of documenting but make sure includes all this conntent at least in spririt. of course laravel likes things brief and simple and clear and linking to further documentation pages for meaty deatils

as i went i found things that were undocumented. document them as you go or make a seperate todo for yourself thanks.

## updated config

changed config names:

-- 'layout' => 'components.layouts.app', -> layout is now 'component_layout' => 'layouts::app', and defaults clearly to the new layouts:: default view namespace and an app file placed in there

-- lazy_placeholder -> this is now called `component_placeholder`

new deafults:
-- new default: `smart_wire_keys` now deafults to true instead of false.

new config items:
'component_locations' => [
        resource_path('views/components'),
        resource_path('views/livewire'),
    ],

this is the array of directories that livewire will look in for sfc/mfc (view-based) components

  'component_namespaces' => [
        'layouts' => resource_path('views/layouts'),
        'pages' => resource_path('views/pages'),
    ],

this is the array of directory namespaces for view-based components


 'make_command' => [
        'type' => 'sfc', // Options: 'sfc', 'mfc', 'class'
        'emoji' => true, // Options: true, false
    ],

this is how yo ucan configure the make command to use emojis/or not emojis for making new view-based components. you can also set the default type. if you set it to 'class' the command will behave as it did in v3.

## new features (link to relevant docs files for each of these of course)

not sure where to note but view-based component files are prefixed withemojis by deatult. you can change make_command config for this and never see the emojis at all. however they are good to:
- distinguish plain blade files from livewire view-based files in your filesystem and editor searching and what now.

- data-loading attribute gets added to every element that triggered a network request. not sure if this is documented.
- wire:[click|...].preserve-scroll="..." - this helper makes it so that before a request comes back and updates the html fo the page it preserves scroll position so there's no layout jump while scrolling or anything. not sure if docuemnted. might need to look at source to determine where it goes
- `wire:intersect`. this is now a new api to mirror alpine's x-intersect. it allows you to run an action when an element enters the viewport. `wire:intersect:enter|leave` are avaialble modifier type things. also things like .once (only fire once) .half (wait till half is vislble/invisible) .full (waits till all of it is inthe viewport to fire) .threshold.50 allows you to manually configure the amoyunt visible needed. margin.200px (allows you to set a margin of intersection) or all of them like so: margin.10%.25px.25.25px
- SLots. components now have slots and attribute bag forwarding {{ $attributes }}
- `wire:ref` allows you to easily access element from js and dispatch events to targeted components inside a loop or something
- `wire:sort` new API to allow drag-sorting of items in a group. suports nesting and drag-between-groups type use-cases
- Livewire now ships with an `$errors` magic that gives you client-side access to the compoennts's error bag (this might need to be documented somewhere actually)
- Islands. obviously this is like the big feature of V4. there are docs for it.
- wire:click="$island('foo', { mode: 'prepend' })" i think is an undocumented feature for directly calling the render of an island and optionally pass a render mode like append or something
- `$intercept` magic. like `$wire.$intercept()` or `this.$intercept()` optionally accepts name of action to intercept and callback with hooks as params. this is doucmented you can reference that.
- `.async` wire:??? modifier and #[Async] attribute.
- `.renderless` is now an avaialble modifier like .async as an alternative to the existing #[Renderless] action/method attribute
- CSP-mode. If you set `csp_safe` to true in your config livewire will now use the alpine CSP build and your whole app will avoid unsafe eval violations. there are many restrictions when using this mode. you can no longer put complex js or global expressions like: `wire:click="doSomething($event.detail.foo)"` or global things like `wire:click="redirectHere(window.location.href)"`
- defer loading in addition to lazy. <livewire:component defer> attribute #[Defer] attribute for class (same signatures as lazy)
- can use additional `lazy.bundle` or `defer.bundle` additional syntax to flag the lazy load to bundle multiple lazy loads instead of them being isolated/parralel. There's also `#[Defer|Lazy(bundle: true)]` for this.
-kinda big one. view-based components can have <script></script> at the bottom with now `@script` wrapper and they work differently. their source is made available via a faux js file to frontend so caching and native module code can work. also they automatically bind this. ($wire is what this is ) and $wire is still available but this. is preferred.


## changed internal behavior:
the request system has been completely overhauled (the stuff im trying to note is basically the interactions.js file)
- make wire:poll non-blocking so that it doesn't block newly initiated wire:clicks and such and also isn't blocked by them
- let wire:model.live erquests run in parallel against each other so that you can type fast and see results quickly

## medium impact breaking changes

### Update hooks now consolidate array/object changes

When replacing an entire array or object from the frontend (e.g., `$wire.items = ['new', 'values']`), Livewire now sends a single consolidated update instead of granular updates for each index.

**Before:** Setting `$wire.items = ['a', 'b']` on an array of 4 items would fire `updatingItems`/`updatedItems` hooks multiple times - once for each index change plus `__rm__` removals.

**After:** The same operation fires the hooks once with the full new array value, matching V2 behavior.

If your code relies on individual index hooks firing when replacing entire arrays, you may need to adjust. Single-item changes (like `wire:model="items.0"`) still fire granular hooks as expected.

## deeper things to know
Livewire.hook('morph',  ({ el, component }) => {
	// Runs just before the child elements in `component` are morphed
})

these still work however if there is a partial morph like used for slot and island morphing you will need to use these new hooks:
Livewire.hook('partial.morph',  ({ startNode, endNode, component }) => {
	// Runs just before partials in `component` are morphed
    //
    // startNode: the comment node marking the beginning of a partial in the DOM.
    // endNode: the comment node marking the end of a partial in the DOM.
})

Livewire.hook('partial.morphed',  ({ startNode, endNode, component }) => {
    // Runs after partials in `component` are morphed
    //
    // startNode: the comment node marking the beginning of a partial in the DOM.
    // endNode: the comment node marking the end of a partial in the DOM.
})

## Deprecations:
Routing to compennts used to be:
Route::get('/foo', Foo::class);
This is still valid however for sfc/mfcs and even class we recommend (enforced for mfc/sfcs (view-based)):
Route::livewire('/foo', Foo::class); or Route::livewire('/foo', 'pages::foo' or whatever)
(this is an important note and might be considered a new feature idk)

This syntax is now deprecated:
```
$wire.$js('action', () => { ... })
```
in favor of this:
```
$wire.$js.action = () => { ... }
```

the 'commit' and 'request' hooks are now deprectated in favor of the interceptors (which are already documented in upgrading.md i thinkg fairly well but use your judgement)


---
bonus quests: (don't do these right awaya just noting them here for later)
- amke morph.partial. or whatever to be morph.fragment instead. just changing all the naming around partial to be fragment because that's what's used internally.
- maybe dn't make $js and $intercept dedicated params to js modules and instead just `this.` and `$wire.` you know?