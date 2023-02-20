
## V3 Upgrade instructions:
From the root directory of a V2 project, you can run the following commands to test out V3:

**Upgrade from V2 to V3:**
```
source <(curl -s https://calebporzio-public.s3.amazonaws.com/upgrade.sh)
```

**Update to latest V3:**
```
composer clear-cache && composer reinstall livewire/livewire
```

**Revert back to V2:**
```
source <(curl -s https://calebporzio-public.s3.amazonaws.com/revert.sh)
```

## Todo:

#### V3 Features
- [x] Auto inject assets
- [x] "locked" properties
- [x] `$parent`
- [x] Reactive properties
- [x] `@teleport`
- [x] `wire:model` props
- [x] `wire:transition`
- [ ] Lazy components
- [ ] PHP Attributes
- [ ] SPA Mode (`wire:navigate`, `@persist`)
- [ ] JavaScript functions
- [ ] `@if` markers
- [ ] Hot-reloading

#### V2 Parity
- [ ] Persistant middleware
- [ ] Eloquent model support
- [ ] Session expriration
- [ ] Detect multiple root elements
- [ ] JS hooks and `Livewire.?`
- [ ] Work through all `@todo` comments
- [ ] Remove all `->markTestSkipped()` statements

#### Finishing touches
- [ ] Unify modern/legacy tests and "TestCase"s
- [ ] Add JS element & component
- [ ] Performance testing
- [ ] Refactor JS (hooks helpers, synthetic, etc.)
- [ ] Finalize internal event names
- [ ] Finalize internal exceptions
- [ ] Brainstorm Form Object solution
- [ ] Brainstorm 3rd party plugin API

#### Documentation
- [ ] Rewrite it lol
