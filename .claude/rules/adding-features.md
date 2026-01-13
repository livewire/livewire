# Adding Features to Livewire

## Feature Categories

Features fall on a spectrum:

1. **Pure JavaScript** - Like a `wire:directive`. May have no PHP logic, but still gets a PHP feature folder for tests.
2. **Full-stack** - Has both JS and PHP portions.
3. **Pure PHP** - Backend-only features.

All categories follow the same organizational patterns.

---

## File Structure

### PHP Side (always required, even for JS-only features)

```
src/Features/Support{FeatureName}/
├── Support{FeatureName}.php    → Main feature file (if needed; extends ComponentHook)
├── Handles{FeatureName}.php    → Trait for Component class (if needed)
├── Base{Thing}Attribute.php    → Base attribute class (if adding attribute)
├── UnitTest.php                → Unit tests
├── BrowserTest.php             → Browser tests
└── fixtures/                   → Test fixtures/stubs (if needed)
```

### JavaScript Side

**For directives** (`wire:foo`):
```
js/directives/wire-{name}.js    → Directive implementation
js/directives/index.js          → Must import the directive
```

**For general JS features**:
```
js/features/{name}.js           → Feature implementation
js/features/index.js            → Must import the feature
```

---

## Registration Checklist

When adding a feature, these files may need updates:

| You created... | Register it in... |
|----------------|-------------------|
| `Support{Feature}.php` | `src/LivewireServiceProvider.php` → `bootFeatures()` array |
| `Handles{Feature}.php` trait | `src/Component.php` → add `use` statement |
| New attribute | `src/Attributes/{AttributeName}.php` (surface file, extends base) |
| New directive JS | `js/directives/index.js` → add import |
| New feature JS | `js/features/index.js` → add import |
| Documentation | `docs/__nav.md` → appropriate section (see Documentation section below) |

---

## PHP Patterns

### Support*.php - Feature Hook Class

Extends `ComponentHook`. Available lifecycle hooks:

```php
namespace Livewire\Features\Support{Feature};

use Livewire\ComponentHook;

class Support{Feature} extends ComponentHook
{
    // Static initialization (runs once at boot)
    static function provide() { }

    // Instance lifecycle hooks
    function boot() { }                                    // Component instance boots
    function mount($params) { }                            // Component mounts
    function hydrate($memo) { }                            // Before each update
    function update($propertyName, $fullPath, $newValue) { } // Property changes (return callable)
    function call($method, $params, $returnEarly) { }      // Before method call (return callable)
    function render($view, $data) { }                      // During render (return callable)
    function dehydrate($context) { }                       // Before response sent
    function destroy() { }                                 // Component destruction
    function exception($e, $stopPropagation) { }           // Exception thrown
}
```

### Handles*.php - Component Trait

Adds public methods to Component class. Uses `store($this)` for state:

```php
namespace Livewire\Features\Support{Feature};

trait Handles{Feature}
{
    public function someMethod()
    {
        store($this)->set('key', $value);
        store($this)->push('array', $item);
        store($this)->get('key');
    }
}
```

### Service Provider Registration

In `src/LivewireServiceProvider.php`, add to the array in `bootFeatures()`:

```php
protected function bootFeatures()
{
    foreach([
        // ... existing features ...
        Features\Support{Feature}\Support{Feature}::class,  // Add here
    ] as $feature) {
        app('livewire')->componentHook($feature);
    }

    ComponentHookRegistry::boot();
}
```

### Component.php Trait Usage

In `src/Component.php`, add the use statement:

```php
abstract class Component
{
    use Handles{Feature};  // Add with other Handles* traits
    // ...
}
```

---

## JavaScript Patterns

### Directive File Structure

If this new directive is really just an alias for an Alpine directive (in the case of `wire:text`, `wire:show`, etc...)
Then the following structure should be used:

```javascript

```

Otherwise, for most other custom livewire directives, use the following structure:

```javascript
import { directive } from "@/directives"

directive('{name}', ({ el, directive, component, $wire, cleanup }) => {
    // directive.expression  → the attribute value (e.g., "user.name")
    // directive.modifiers   → array of modifiers (e.g., ['live', 'debounce'])
    // el                    → the DOM element
    // component             → Livewire component instance
    // $wire                 → shorthand for component.$wire
    // cleanup               → function to register teardown logic

    // Often uses Alpine.bind() for reactivity:
    Alpine.bind(el, {
        ['x-effect']() {
            // reactive logic
        }
    })

    // Register cleanup if needed:
    cleanup(() => {
        // teardown logic
    })
})
```

### Directive Registration

In `js/directives/index.js`, add an import:

```javascript
import './wire-{name}'
```

The import itself triggers registration (the `directive()` call runs on import).

---

## Attribute Pattern

Attributes have a two-file pattern for clean namespacing:

**Surface file** (`src/Attributes/{Name}.php`): (targets vary based on needs of feature)
```php
namespace Livewire\Attributes;

use Livewire\Features\Support{Feature}\Base{Name}Attribute as Base;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class {Name} extends Base { }
```

**Base file** (`src/Features/Support{Feature}/Base{Name}Attribute.php`):
```php
namespace Livewire\Features\Support{Feature};

use Livewire\Attribute;

#[\Attribute]
class Base{Name}Attribute extends Attribute
{
    // Actual implementation here
}
```

---

## Naming Conventions

- Feature folders: `Support{FeatureName}` (e.g., `SupportSlots`, `SupportFileUploads`)
- Traits: `Handles{FeatureName}` (plural "Handles", e.g., `HandlesSlots`)
- Directives: `wire-{name}.js` (e.g., `wire-model.js`, `wire-navigate.js`)
- Keep names single-word when possible (e.g., `wire:click.stop` not `wire:click.stop-propagation`)

---

## Testing

Tests live inside the feature folder:

- `UnitTest.php` - PHP unit tests
- `BrowserTest.php` - Browser/Dusk tests (used even for JS-only features)

When writing tests try to keep them simple and expressive. Reference other test files for common patterns...

Run tests:
```bash
phpunit --testsuite="Unit" src/Features/Support{Feature}/UnitTest.php
phpunit --testsuite="Browser" src/Features/Support{Feature}/BrowserTest.php
```

---

## Build Step

After JS changes, run:
```bash
npm run build
```

---

## Documentation

Documentation lives in the `docs/` directory.

### File Structure

```
docs/
├── __nav.md                → Navigation menu (YAML format)
├── wire-{name}.md          → Documentation for wire: directives
├── attribute-{name}.md     → Documentation for PHP attributes
├── directive-{name}.md     → Documentation for Blade directives
└── {feature}.md            → Documentation for general features
```

### Registration Checklist

| You created... | Register it in... |
|----------------|-------------------|
| `wire-{name}.md` | `docs/__nav.md` → `HTML Directives:` section (alphabetically) |
| `attribute-{name}.md` | `docs/__nav.md` → `PHP Attributes:` section (alphabetically) |
| `directive-{name}.md` | `docs/__nav.md` → `Blade Directives:` section |
| General feature docs | `docs/__nav.md` → `Features:` section |

### Documentation Format

Follow this structure for directive documentation:

```markdown

`wire:{name}` is a directive that [brief description].

[Compare to similar features or Alpine equivalents if applicable]

## Basic usage

[Practical example with PHP component and Blade template]

## [Additional sections as needed]

[More examples, edge cases, or advanced usage]

## Reference

```blade
wire:{name}="expression"
```

[List modifiers if any, or note "This directive has no modifiers."]
```

### Navigation Entry Format

In `docs/__nav.md`, add entries in YAML format:

```yaml
wire:{name}: { uri: /docs/4.x/wire-{name}, file: /wire-{name}.md }
```
