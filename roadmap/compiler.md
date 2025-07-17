## Livewire component compiler system

Currently, in V3, there is no Livewire component comiler. Components are normal classes in the user's filesystem that extends \Livewire\Component.

The only thing compiled is when the component renders, it renders a Blade view, and like any Blade view, Blade will ensure that the referenced view is resolved to a blade file and then compiled to raw PHP and evalauted.

However, in V4 we are using a new single-file, view-first, system (as described in [](single-file-components.md))

Because these single file components aren't runnable Blade files, we will need a compilation step that converts one of them into a usable view and a usable class.

So given the new registry system as described in [](registry.md) that will know how to convert a name into a full view file path, this file is all about describing how to convert that file path into usable runtime or compile time classes and views.

## Current V4 Implementation Status

### What's Been Implemented

The complete V4 single-file component compiler system has been implemented with comprehensive parsing, compilation, caching, and file generation capabilities.

### File Structure

The V4 compiler system is organized in:

```
src/V4/Compiler/
├── SingleFileComponentCompiler.php
├── CompilationResult.php
├── ParsedComponent.php
├── ParsedComponentUnitTest.php
├── CompilationResultUnitTest.php
├── SingleFileComponentCompilerUnitTest.php
└── Exceptions/
    ├── CompilationException.php
    ├── ParseException.php
    └── InvalidComponentException.php
```

### Core Classes

#### 1. **SingleFileComponentCompiler**
**Location**: `src/V4/Compiler/SingleFileComponentCompiler.php`
**Namespace**: `Livewire\V4\Compiler`

The main compiler class that handles parsing, compilation, and caching of single-file components.

**Key Methods**:
```php
// Compile a view file into usable class and view files
public function compile(string $viewPath): CompilationResult

// Compile a multi-file component directory into usable class and view files
public function compileMultiFileComponent(string $directory): CompilationResult

// Check if a component is already compiled and up-to-date
public function isCompiled(string $viewPath, ?string $hash = null): bool

// Get the path to the compiled class file
public function getCompiledPath(string $viewPath): string
```

#### 2. **CompilationResult**
**Location**: `src/V4/Compiler/CompilationResult.php`

Represents the result of compiling a single-file component, containing all generated paths and metadata.

**Properties**:
```php
public string $className;      // e.g., "Livewire\Compiled\Counter_abc123"
public string $classPath;      // e.g., "/storage/framework/views/livewire/classes/Counter_abc123.php"
public string $viewName;       // e.g., "livewire-compiled::counter_abc123"
public string $viewPath;       // e.g., "/storage/framework/views/livewire/views/counter_abc123.blade.php"
public bool $isExternal;       // true if using external class reference
public ?string $externalClass; // e.g., "App\Livewire\Counter" if external
public string $hash;           // Cache invalidation hash
```

#### 3. **ParsedComponent**
**Location**: `src/V4/Compiler/ParsedComponent.php`

Represents the parsed result of a single-file component with frontmatter and view content separated.

**Properties**:
```php
public string $frontmatter;    // The @php block content
public string $viewContent;    // The Blade template part
public bool $isExternal;       // Whether it references external class
public ?string $externalClass; // External class name if applicable
```

### Component Parsing

The compiler supports two component formats:

#### 1. **Inline Components**
```php
@php
new class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>
```

#### 2. **External Components**
```php
@php(new App\Livewire\Counter)

<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>
```

#### 3. **Components with Class-Level Attributes**

##### Compact Syntax
```php
@php
new #[Layout('layouts.app')] class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>
```

##### Spaced Syntax
```php
@php
new
#[Layout('layouts.app')]
#[Lazy]
class extends Livewire\Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
}
@endphp

<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>
```

##### Combined with @layout Directive
```php
@layout('layouts.main')

@php
new #[Lazy] class extends Livewire\Component {
    public $count = 0;
}
@endphp

<div>Count: {{ $count }}</div>
```

#### 4. **Components with Grouped Imports**
```php
@php

use App\Models\Post;
use Livewire\Attributes\{Computed, Locked, Validate, Url, Session};
use Illuminate\Support\{Str, Collection, Carbon};

new class extends Livewire\Component {
    public Post $post;
    public Collection $items;

    #[Computed]
    #[Locked]
    public function title()
    {
        return Str::title($this->post->title);
    }

    #[Validate('required')]
    #[Session]
    public $sessionData = [];
}
@endphp

<div>
    <h1>{{ $title }}</h1>
</div>
```

#### 5. **Multi-File Components**
The compiler also supports a directory-based approach where the frontmatter and view content are separated into individual files.

##### Directory Structure
```
counter/
├── counter.livewire.php  (frontmatter/class)
└── counter.blade.php     (view content)
```

##### Example Files

**counter/counter.livewire.php**
```php
<?php

use Livewire\Attributes\Computed;

new class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    #[Computed]
    public function doubleCount()
    {
        return $this->count * 2;
    }
}
```

**counter/counter.blade.php**
```html
<div>
    <h2>Count: {{ $count }}</h2>
    <p>Double: {{ $doubleCount }}</p>
    <button wire:click="increment">+</button>
</div>
```

##### Compilation Process for Multi-File Components
```php
use Livewire\V4\Compiler\SingleFileComponentCompiler;

$compiler = new SingleFileComponentCompiler();

// Compile a multi-file component directory
$result = $compiler->compileMultiFileComponent('/path/to/counter');
```

The multi-file compilation process:
1. **Validates directory structure** - Ensures directory exists and contains required files
2. **Reads both files** - Loads `counter.livewire.php` and `counter.blade.php`
3. **Concatenates content** - Combines them into standard format: `@php frontmatter @endphp view_content`
4. **Uses standard pipeline** - Continues with normal parsing and compilation process
5. **Generates same output** - Creates identical class and view files as single-file components

This approach provides **separation of concerns** while maintaining full compatibility with all existing features (computed properties, layouts, class-level attributes, etc.).

### Compilation Process

The compilation process works as follows:

1. **Parse the component file** - Extract frontmatter and view content
2. **Generate cache hash** - Based on file path, content, and modification time
3. **Check cache** - Return existing compilation if up-to-date
4. **Generate class and view names** - Create unique names with hash suffix
5. **Generate files** - Create compiled class and view files
6. **Return CompilationResult** - With all paths and metadata

### Generated Files

#### Generated Class File Example
```php
// storage/framework/views/livewire/classes/Counter_abc123.php
<?php

namespace Livewire\Compiled;

class Counter_abc123 extends \Livewire\Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire-compiled::counter_abc123');
    }
}
```

#### Generated Class File with Class-Level Attributes Example
```php
// storage/framework/views/livewire/classes/Counter_def456.php
<?php

namespace Livewire\Compiled;

#[Layout('layouts.app')]
#[Lazy]
class Counter_def456 extends \Livewire\Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire-compiled::counter_def456');
    }
}
```

#### Generated Class File with Both @layout Directive and Class-Level Attributes
```php
// storage/framework/views/livewire/classes/Counter_ghi789.php
<?php

namespace Livewire\Compiled;

#[\Livewire\Attributes\Layout('layouts.main')]
#[Lazy]
class Counter_ghi789 extends \Livewire\Component
{
    public $count = 0;

    public function render()
    {
        return view('livewire-compiled::counter_ghi789');
    }
}
```

#### Generated Class File with Grouped Imports
```php
// storage/framework/views/livewire/classes/BlogPost_jkl012.php
<?php

namespace Livewire\Compiled;

use App\Models\Post;
use Livewire\Attributes\{Computed, Locked, Validate, Url, Session};
use Illuminate\Support\{Str, Collection, Carbon};

class BlogPost_jkl012 extends \Livewire\Component
{
    public Post $post;
    public Collection $items;

    #[Computed]
    #[Locked]
    public function title()
    {
        return Str::title($this->post->title);
    }

    #[Validate('required')]
    #[Session]
    public $sessionData = [];

    public function render()
    {
        return view('livewire-compiled::blog-post_jkl012');
    }
}
```

#### Generated View File Example
```php
{{-- storage/framework/views/livewire/views/counter_abc123.blade.php --}}
<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>
```

### Cache System

The compiler implements a sophisticated caching system:

- **Cache Directory**: `storage/framework/views/livewire/`
  - `/classes/` - Compiled PHP class files
  - `/views/` - Cleaned Blade view files
- **Hash-based invalidation** - Files are regenerated when source changes
- **Automatic directory creation** - Cache directories are created as needed

### Usage Examples

#### Basic Usage
```php
use Livewire\V4\Compiler\SingleFileComponentCompiler;

$compiler = new SingleFileComponentCompiler();

// Compile a component
$result = $compiler->compile('/path/to/counter.blade.php');

echo $result->className;  // "Livewire\Compiled\Counter_abc123"
echo $result->viewName;   // "livewire-compiled::counter_abc123"
```

#### Custom Cache Directory
```php
$compiler = new SingleFileComponentCompiler('/custom/cache/path');
$result = $compiler->compile($viewPath);
```

#### Check Compilation Status
```php
if ($compiler->isCompiled($viewPath)) {
    echo "Already compiled!";
} else {
    $result = $compiler->compile($viewPath);
}
```

### Error Handling

The compiler provides specific exceptions for different error scenarios:

- **CompilationException** - General compilation errors (missing files, etc.)
- **ParseException** - When parsing @php blocks fails
- **InvalidComponentException** - When component format is invalid

### Features Implemented

#### ✅ **Dual Component Support**
- Inline anonymous classes with `@php ... @endphp`
- External class references with `@php(new ClassName)`

#### ✅ **Intelligent Parsing**
- Regex-based extraction of frontmatter and view content
- Support for both `new ClassName` and `new ClassName::class` syntax
- Validation of class definitions in @php blocks

#### ✅ **Robust Caching**
- Content and timestamp-based hash generation
- Automatic cache invalidation when files change
- Efficient cache hit detection

#### ✅ **Clean File Generation**
- Proper namespace generation for compiled classes
- Automatic render() method injection
- Stripped view files without @php blocks

#### ✅ **Component Name Normalization**
- Handles dashes, underscores, and special characters
- Generates valid PHP class names
- Creates descriptive view names

#### ✅ **Comprehensive Testing**
- **90 unit tests** covering all functionality
- **343 assertions** ensuring correctness
- Tests for parsing, compilation, caching, layout, naked scripts, computed properties (including in islands), and error scenarios

#### ✅ **Layout Directive Support**
- Parses `@layout()` directives from component frontmatter
- Compiles to `#[Layout]` attributes on generated classes
- Supports both simple layouts and layouts with data arrays
- Works with both inline and external components

#### ✅ **Naked Script Transformation**
- Automatically detects naked `<script>` tags in component views
- Wraps them with `@script`/`@endscript` directives during compilation
- Preserves all script attributes and handles multiple scripts
- Skips components that already have `@script` directives

#### ✅ **Computed Property Transformation**
- Transforms `{{ $computedProperty }}` to `{{ $this->computedProperty }}` in main view content
- Uses guard statements in inline islands: `<?php if (! isset($computedProperty)) $computedProperty = $this->computedProperty; ?>`
- Preserves JIT evaluation while providing clean syntax
- Validates against variable reassignment conflicts in main views
- Supports all computed attribute syntaxes and visibility modifiers
- Works consistently across main view content and `@island()...@endisland` blocks
- **Guard approach allows custom data to override computed properties in islands**

#### ✅ **Inline InlineIslands Support**
- Processes `@island()...@endisland` blocks into separate view files
- Generates unique island view names with content-based hashing
- Creates island lookup properties in compiled classes
- Supports island data passing and complex nested scenarios
- Applies view transformations (naked scripts) and computed property guards to island content
- **Intelligent computed property handling**: Uses guard statements instead of transformation for better data flexibility

#### ✅ **Use Statement Preservation**
- Extracts and preserves `use` statements from frontmatter
- Maintains import aliases and fully qualified names
- Properly places use statements in generated class files
- Works with both simple imports and aliased imports

#### ✅ **Enhanced Pre-Class Code Preservation**
- Preserves all PHP code before the class definition verbatim
- Supports grouped imports: `use Livewire\Attributes\{Computed, Locked, Validate};`
- Handles simple imports: `use App\Models\Post;`
- Supports aliased imports: `use App\Models\Post as PostModel;`
- Preserves constants, functions, and any other valid PHP code
- Robust parsing that avoids edge cases with complex PHP syntax
- Works with both `@php ... @endphp` and `<?php ... ?>` syntax

#### ✅ **Class-Level Attributes Support**
- Preserves PHP attributes defined before the `class` keyword in inline components
- Supports both compact syntax: `new #[Layout('layouts.app')] class extends...`
- Supports spaced/newlined syntax: `new\n#[Layout('layouts.app')]\nclass extends...`
- Handles multiple attributes: `new #[Layout('layouts.app')] #[Lazy] class extends...`
- Works with both `@php ... @endphp` and `<?php ... ?>` syntax
- Integrates seamlessly with `@layout()` directives (both can be used together)
- Maintains backwards compatibility - components without class-level attributes work unchanged

#### ✅ **Multi-File Component Support**
- Supports compiling a directory of frontmatter/class and view files
- Handles validation, parsing, compilation, and caching for multi-file components
- Generates identical class and view files as single-file components
- Maintains all existing features (computed properties, layouts, class-level attributes, etc.)

### Test Coverage

**Test Command**: `phpunit src/V4/Compiler/ --testdox`

#### ParsedComponent Tests (10 tests)
- Component creation (inline/external)
- Class detection methods
- Definition extraction

#### CompilationResult Tests (8 tests)
- Result creation and properties
- Class generation flags
- Namespace/class name extraction

#### SingleFileComponentCompiler Tests (72 tests)
- Inline and external component compilation
- Error handling and validation
- Generated file content verification
- Caching and invalidation
- Name normalization
- Directory management
- Layout directive processing
- Naked script transformation
- Computed property transformation (with guard statements in islands)
- Custom data override support in islands
- Inline islands processing
- Enhanced pre-class code preservation (grouped imports, constants, etc.)
- Traditional PHP tag support
- Class-level attributes support (compact and spaced syntax)
- Class-level attributes integration with layout directives
- Backwards compatibility verification

### Integration Points

#### With Registry System
```php
use Livewire\V4\Registry\ComponentViewPathResolver;
use Livewire\V4\Compiler\SingleFileComponentCompiler;

// Resolve component name to view path
$resolver = new ComponentViewPathResolver();
$viewPath = $resolver->resolve('counter');

// Compile the view into usable class and view
$compiler = new SingleFileComponentCompiler();
$result = $compiler->compile($viewPath);

// Now you have a compiled class ready for instantiation
$component = new $result->className();
```

#### Future Integration Goals
1. **Autoloader Registration** - Register compiled classes with PSR-4 autoloading
2. **View System Integration** - Register compiled views with Laravel's view system
3. **Service Provider Integration** - Wire into Livewire's service provider
4. **Development Tools** - Cache clearing commands and file watching

### Performance Characteristics

- **One-time compilation cost** - Files only compiled when changed
- **Minimal runtime overhead** - Compiled files are standard PHP/Blade
- **Efficient cache hits** - Quick hash-based cache validation
- **Automatic cleanup** - Old compiled files can be safely removed

### Next Steps for Full Integration

1. **Service Provider Integration** - Register compiler in Livewire's service provider
2. **Autoloader Setup** - Make compiled classes discoverable via autoloading
3. **Bridge with V3 Registry** - Integrate with existing component resolution
4. **View Provider Registration** - Register compiled views with Laravel
5. **Development Experience** - Add Artisan commands and better error reporting

The compiler system is now feature-complete and ready for integration with the broader V4 system. It provides a solid foundation for the view-first component architecture while maintaining excellent performance through intelligent caching.

---

# Implementation Guide for Future Development

## 🤖 Instructions for AI/LLM Assistance

**IMPORTANT**: When making changes to the V4 compiler system, follow these steps:

### 1. Documentation Updates Required
After implementing any compiler feature or fix, you MUST update this `roadmap/compiler.md` file to reflect:
- New features added to the "Features Implemented" section
- Updated test counts (see Test Coverage section)
- Enhanced transformation pipeline descriptions
- New patterns or examples in the Implementation Guide

### 2. Testing Commands
**DO NOT use `php artisan test`** - it doesn't work for this project.

**Correct test commands:**
```bash
# Run all compiler tests
vendor/bin/phpunit src/V4/Compiler/ --testdox

# Run specific compiler class tests
vendor/bin/phpunit src/V4/Compiler/SingleFileComponentCompilerUnitTest.php --testdox

# Run specific test by name
vendor/bin/phpunit src/V4/Compiler/SingleFileComponentCompilerUnitTest.php --filter="test_name" --testdox

# Get test counts for documentation updates
vendor/bin/phpunit src/V4/Compiler/ | tail -3
vendor/bin/phpunit src/V4/Compiler/SingleFileComponentCompilerUnitTest.php | tail -3
```

### 3. Common Documentation Update Locations
When adding new compiler features, update these sections:
- **Features Implemented** (around line 230): Add ✅ **New Feature Name**
- **Test Coverage** (around line 280): Update test counts and descriptions
- **Transformation Pipeline** (around line 360): Update code examples if transformation chain changes
- **Common Extension Scenarios** (around line 550): Add new patterns if applicable

### 4. Validation Checklist
Before completing any compiler work:
- [ ] All tests pass: `vendor/bin/phpunit src/V4/Compiler/`
- [ ] Documentation updated in this file
- [ ] Test counts are accurate in documentation
- [ ] New features described with examples
- [ ] Code patterns documented for future reference

---

## Architecture Overview

### Transformation Pipeline

The compiler uses a multi-stage transformation pipeline in the `compile()` method:

```php
public function compile(string $viewPath): CompilationResult
{
    // 1. File validation and content loading
    $content = File::get($viewPath);
    $hash = $this->generateHash($viewPath, $content);

    // 2. Cache check (early return if up-to-date)
    if ($this->isCompiled($viewPath, $hash)) {
        return $this->getExistingCompilationResult($viewPath, $hash);
    }

    // 3. Parsing stage
    $parsed = $this->parseComponent($content);

    // 4. Result generation
    $result = $this->generateCompilationResult($viewPath, $parsed, $hash);

    // 5. File generation
    $this->generateFiles($result, $parsed);

    return $result;
}
```