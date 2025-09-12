
# Livewire Make Command Specification

## Overview

The `php artisan make:livewire` command creates Livewire components with support for three different component types. This document outlines the expected behavior and configuration options.

## Basic Usage

### Interactive Mode

```bash
php artisan make:livewire
```

When run without arguments, the command should prompt the user for a component name using Laravel's prompt system. After entering the name and pressing Enter, the component will be created.

### Direct Mode

```bash
php artisan make:livewire counter
```

When provided with a component name, the command creates a single-file component named `⚡counter.blade.php` (with lightning bolt emoji) in the first configured directory from the Livewire config. The config key is `livewire.component_locations`, and the first entry determines the creation directory.

## Component Types

Livewire 4 supports three types of components:

1. **Single-file components (sfc)** - Default
2. **Multi-file components (mfc)**
3. **Class-based components**

### Command Flags

- `--MFC` - Creates a multi-file component
- `--class` - Creates a class-based component
- `--type=sfc|mfc|class` - Alternative way to specify component type
- `--emoji` - Controls emoji usage in file/directory names

## Component Creation Details

### Class-based Components

**Location**: `app/Livewire/` directory

**Files Created**:
- Class file: `app/Livewire/{ComponentName}.php`
- View file: `resources/views/Livewire/{ComponentName}.blade.php`

**Structure**: Fully namespaced component class with a `render()` method that returns a Blade view named `livewire.{component-name}`.

**Example**: For component "Counter"
- Class: `app/Livewire/Counter.php`
- View: `resources/views/livewire/counter.blade.php`

### Single-file Components

**Location**: First configured component location directory (default: `resources/views/components`)

**File Created**: `⚡{component-name}.blade.php`

**Structure**: Single file containing an anonymous class at the top and Blade markup below.

**Example**: For component "counter"
- File: `resources/views/components/⚡counter.blade.php`

### Multi-file Components

**Location**: Same as single-file components, but creates a directory instead

**Directory Created**: `⚡{component-name}/`

**Files Created**:
- `{component-name}.php` - Anonymous class
- `{component-name}.blade.php` - Blade markup
- `{component-name}.js` - JavaScript (optional)
- `{component-name}.test.php` - Pest tests (optional)

**Example**: For component "counter" with `--mfc` flag
- Directory: `resources/views/components/⚡counter/`
- Files: `counter.php`, `counter.blade.php`, `counter.js`, `counter.test.php`

## Configuration

Configuration is managed through `config/livewire.php` under the `make_command` key:

```php
'make_command' => [
    'type' => 'sfc', // Default component type: sfc, mfc, or class
    'emoji' => true, // Whether to use emoji in file/directory names
]
```

### Configuration Priority

1. Command-line flags take precedence over configuration (Default behavior: create sfc with emoji enabled)
2. If no flags are provided, configuration values are used

## Existing Component Handling

### General Behavior

If a component already exists, the command displays "Component already exists" and exits, except for single-file components.

### Single-file Component Upgrade

When attempting to create a component that already exists as a single-file component, the command prompts:

> "Would you like to upgrade this component to a multi-file component?"

- **Yes**: Splits the single-file component into a multi-file component structure
- **No**: Exits without making changes

## Stub Files

The command uses internal stub files for component creation. Users can publish and customize these stubs:

### Available Stubs

- **Single-file component**: Complete component stub
- **Class-based component**:
  - Class stub
  - Associated Blade view stub
- **Multi-file component**:
  - Class stub
  - Blade view stub
  - JavaScript stub (optional)
  - Test file stub (optional)

### Customization

Users can publish stub files using Laravel's stub publishing mechanism to customize the generated component structure and content.

### Stub definitions

The following stub files are used for component generation:


#### Class-based Component Stubs

**Class Stub**: `stubs/livewire.stub`
```php
<?php

namespace App\Livewire;

use Livewire\Component;

class [CLASS_NAME] extends Component
{
    public function render()
    {
        return view('livewire.[VIEW_NAME]');
    }
}
```

**View Stub**: `stubs/livewire.view.stub`
```blade
<div>
    {{-- [INSPIRATION] --}}
</div>
```

#### Single-file Component Stub
**File**: `stubs/livewire.sfc.stub`

**Structure**:
```php
<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    {{-- [INSPIRATION] --}}
</div>
```


#### Multi-file Component Stubs

**Class Stub**: `stubs/livewire.mfc-class.stub`
```php
<?php

use Livewire\Component;

new class extends Component
{
    //
};
```

**View Stub**: `stubs/livewire.mfc-view.stub`
```blade
<div>
    {{-- [INSPIRATION] --}}
</div>
```

**JavaScript Stub**: `stubs/livewire.mfc-js.stub`
```javascript
// Add your JavaScript here
```

**Test Stub**: `stubs/livewire-mfc-test.stub`
```php
<?php

it('renders successfully', function () {
    Livewire::test('[component-name]')
        ->assertStatus(200);
});
```

#### Stub Variables

The following variables are available for replacement in stub files:

- `[CLASS_NAME]` - PascalCase component name (e.g., "UserProfile")
- `[VIEW_NAME]` - Kebab-case view name (e.g., "user-profile")
- `[component-name]` - Kebab-case component name (e.g., "user-profile")
- `[INSPIRATION]` - Inspirational comment text (e.g., "Success is as dangerous as failure.")


## Implementation

The implementation will be completed in two main phases:

### Phase 1: Extend Finder Class

The `Finder` class needs to be extended with new methods to support component creation. Currently, `Finder` only has methods for resolving existing components, but we need methods for determining creation paths.

#### New Methods Required

**For Single-file Components:**
- `resolveSingleFileComponentPathForCreation($name)` - Determines the file path where a single-file component should be created

**For Class-based Components:**
- `resolveClassComponentFilePaths($name)` - Returns an array with keys `class` and `view` containing the file paths for both the PHP class and Blade view

**For Multi-file Components:**
- `resolveMultiFileComponentPathForCreation($name)` - Determines the directory path where a multi-file component should be created

#### Key Design Principles

- These methods should **not** check if files exist (since they don't exist yet)
- They should leverage the existing registered view locations and namespaces
- All path resolution logic should be centralized in the `Finder` class
- Unit tests must be added for all new methods

### Phase 2: Implement Command Class

The existing `MakeCommand` class should be completely rewritten from scratch, following Laravel's command patterns.

#### Command Class Requirements

- Clean, simple implementation following Laravel conventions
- Use Laravel prompts for modern, interactive interfaces
- Leverage the new `Finder` methods (using app('livewire.finder')->... )for path resolution
- Handle existing component detection and upgrade prompts
- Use stub files for component generation

#### Upgrade Functionality

The command should include logic to upgrade existing single-file components to multi-file components when requested by the user.

### Phase 3: Create Stub Files

All stub files specified in this document must be created in the `stubs/` directory:

- `stubs/livewire.stub` (class-based component class - already exists but maybe needs to be updated to match new replacement syntaxes/names)
- `stubs/livewire.view.stub` (class-based component view - same as above)
- `stubs/livewire.sfc.stub` (single-file component)
- `stubs/livewire.mfc-class.stub` (multi-file component class)
- `stubs/livewire.mfc-view.stub` (multi-file component view)
- `stubs/livewire.mfc-js.stub` (multi-file component JavaScript)
- `stubs/livewire-mfc-test.stub` (multi-file component test)

### Implementation Benefits

This approach provides several advantages:

- **Separation of Concerns**: Path resolution logic stays in `Finder`, command logic stays in `MakeCommand`
- **Testability**: Each component can be thoroughly unit tested
- **Maintainability**: Clean, Laravel-conventional code structure
- **Extensibility**: Easy to add new component types or modify existing behavior
- **Reliability**: Comprehensive testing ensures the command works correctly in all scenarios