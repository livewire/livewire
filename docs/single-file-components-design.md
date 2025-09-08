# Single-File & Multi-File Components Design

## Overview

Livewire v4 introduces two new component formats alongside the existing class-based components:

1. **Single-File Components**: A single Blade file (optionally prefixed with ⚡) containing:
   - PHP class definition in frontmatter
   - HTML template
   - Optional `<script>` tag for JavaScript

2. **Multi-File Components**: A folder (optionally prefixed with ⚡) containing separate files:
   - `.blade.php` - View template
   - `.php` - PHP class
   - `.js` - JavaScript (optional)
   - `.test.php` - Tests (optional)

## Current Implementation Status

- V4 components use a "black box" approach via `ResolveMissingComponent` hook
- Falls back to v4 lookup when v3 registry doesn't find a component
- Completely isolated from v3 component registry
- No risk to existing v3 functionality

## Design Philosophy

The key principle: **One Livewire, Multiple Flavors**

Developers should see Livewire components as a single concept that can be authored in different ways based on their preference:
- Class-based (traditional, good for complex logic)
- Single-file (quick, good for simple components)
- Multi-file (organized, good for larger components with tests)

The API and mental model should be completely unified. The implementation can be modular internally, but that's an implementation detail users never see.

### 2. Namespace Registration

Current v3 uses class-based namespaces:
```php
Livewire::component('admin.users', App\Livewire\Admin\Users::class);
```

V4 needs directory-based namespaces:
```php
// For single/multi-file components
Livewire::namespace('pages', resource_path('views/livewire/pages'));
Livewire::namespace('layouts', resource_path('views/livewire/layouts'));
```

**Questions:**
- Should we unify namespace registration?
- Auto-detect namespace type (class vs directory)?
- Support both types in parallel?

### 3. Component Discovery Priority

When looking up `<livewire:user.profile />`:

1. Check v3 class registry
2. Check v4 location registry
3. Check default locations

Or should v4 take priority as the "new way"?

## Proposed Approach: Unified but Smart

### Single, Harmonious API

The API should feel unified and natural:

```php
// Register a class namespace or view path for global component lookups...
Livewire::finder()->addLocation(classNamespace: App\Livewire::class);
Livewire::finder()->addLocation(viewPath: resource_path('views/livewire'));

// Register a class namespace or view path for namespaced component lookups...
Livewire::finder()->addNamespace('pages', classNamespace: App\Pages::class);
Livewire::finder()->addNamespace('pages', viewPath: resource_path('views/livewire'));

// Register a single component...
Livewire::finder()->addComponent('user.profile', UserProfile::class); // Class-based component...
Livewire::finder()->addComponent('user.profile', path: resource_path('views/livewire/user/profile.blade.php')); // Single file component...
Livewire::finder()->addComponent('user.profile', path: resource_path('views/livewire/user/profile')); // Multi-file component...
```

### Global Locations vs Namespaced Locations

**Default behavior:**
- `resources/views/livewire/` is the default global location
- Looking up `<livewire:counter />` checks the default location

**Adding global locations:**
```php
// These directories get checked for any component lookup
Livewire::addLocation(resource_path('views/shared/components'));
Livewire::addLocation(storage_path('app/dynamic-components'));

// Now <livewire:counter /> will search in:
// 1. resources/views/livewire/
// 2. resources/views/shared/components/
// 3. storage/app/dynamic-components/
```

**Namespaced locations:**
```php
// These require a namespace prefix
Livewire::namespace('admin', resource_path('views/livewire/admin'));

// Only accessible as <livewire:admin.users />
// NOT accessible as <livewire:users />
```

This mirrors how Blade views work with `View::addLocation()` for global paths and `View::addNamespace()` for prefixed paths.

### Smart Detection Under the Hood

```php
class ComponentRegistry {
    public function namespace($name, $target) {
        if (is_dir($target) || Str::contains($target, '/')) {
            // It's a directory - register for file-based components
            $this->fileNamespaces[$name] = $target;
        } elseif (class_exists($target) || Str::contains($target, '\\')) {
            // It's a class namespace - register for class-based
            $this->classNamespaces[$name] = $target;
        }
    }

    public function find($name) {
        // Try all methods in a sensible order
        // 1. Explicit registrations (component())
        // 2. File-based components (single-file, multi-file)
        // 3. Class-based components
        // User never knows or cares about the difference
    }
}
```

### Implementation Strategy

Keep internal separation but present unified interface:

```php
class ComponentRegistry {
    protected $explicitComponents = [];     // component() calls
    protected $classNamespaces = [];       // class-based namespaces
    protected $fileNamespaces = [];        // directory-based namespaces
    protected $globalLocations = [];       // addLocation() calls

    public function component($name, $class) {
        // Existing behavior - highest priority
        $this->explicitComponents[$name] = $class;
    }

    public function namespace($name, $target) {
        if ($this->isDirectory($target)) {
            $this->fileNamespaces[$name] = $target;
        } else {
            $this->classNamespaces[$name] = $target;
        }
    }

    public function addLocation($path) {
        $this->globalLocations[] = $path;
    }

    public function find($name) {
        // 1. Explicit registrations (highest priority)
        if (isset($this->explicitComponents[$name])) {
            return $this->explicitComponents[$name];
        }

        // 2. Namespace-based lookup (file then class)
        if ($component = $this->findInFileNamespaces($name)) {
            return $component;
        }

        if ($component = $this->findInClassNamespaces($name)) {
            return $component;
        }

        // 3. Global locations (including default + addLocation())
        if ($component = $this->findInGlobalLocations($name)) {
            return $component;
        }

        return null;
    }

    protected function findInGlobalLocations($name) {
        // Check default location first
        $locations = [
            resource_path('views/livewire'),
            ...$this->globalLocations
        ];

        foreach ($locations as $location) {
            if ($component = $this->findInLocation($name, $location)) {
                return $component;
            }
        }

        return null;
    }

    protected function isDirectory($target) {
        return is_dir($target) ||
               (!class_exists($target) && !str_contains($target, '\\'));
    }
}
```

### Real-World Usage

Developers just register namespaces naturally:

```php
// In AppServiceProvider::boot()
Livewire::namespace('admin', resource_path('views/livewire/admin'));
Livewire::namespace('pages', resource_path('views/livewire/pages'));

// Works for class namespaces too
Livewire::namespace('legacy', App\Http\Livewire\Legacy::class);
```

Then in views, it just works:
```blade
<livewire:admin.users />
<livewire:pages.dashboard />
<livewire:legacy.old-component />
```

No mental overhead about which system is being used.

## Implementation Considerations

### File Structure Examples

**Single-File Component:**
```
resources/views/livewire/
  ⚡user-profile.blade.php
```

**Multi-File Component:**
```
resources/views/livewire/
  ⚡user-profile/
    view.blade.php
    class.php
    script.js
    test.php
```

### Discovery Logic

```php
// Pseudo-code for unified discovery
function findComponent($name) {
    // 1. Try v3 class registry
    if ($class = $this->v3Registry->find($name)) {
        return $class;
    }

    // 2. Try v4 registry
    if ($component = $this->v4Registry->find($name)) {
        return $component;
    }

    // 3. Try default locations
    return $this->findInDefaultLocations($name);
}
```

## Next Steps

1. [ ] Decide on integration approach (black box vs unified)
2. [ ] Design namespace registration API
3. [ ] Define component discovery priority
4. [ ] Implement v4 namespace support
5. [ ] Create migration guide for v3 → v4

## Open Questions

1. Should ⚡ prefix be required or optional?
2. How to handle naming conflicts between v3/v4 components?
3. Should we support mixed component types in same namespace?
4. What about component aliases and shortcuts?
5. Performance implications of checking multiple registries?