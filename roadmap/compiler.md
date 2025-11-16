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
├── counter.livewire.php  (frontmatter/class definition)
├── counter.blade.php     (view content)
└── counter.js            (📦 OPTIONAL: dedicated JavaScript)
```

##### Example Files

**counter/counter.livewire.php**
```php
use Livewire\Attributes\Computed;

new class extends Component {
    public $count = 0;

    public function increment() {
        $this->count++;
    }

    #[Computed]
    public function doubleCount() {
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

**counter/counter.js** ✨ **NEW**
```javascript
console.log('Counter component loaded!');

document.addEventListener('DOMContentLoaded', function() {
    console.log('Counter ready!');

    // Component-specific JavaScript logic
    window.counterUtils = {
        formatCount: (count) => count.toLocaleString()
    };
});
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
2. **Reads required files** - Loads `counter.livewire.php` and `counter.blade.php`
3. **Reads optional files** ✨ **NEW** - Loads `counter.js` if present
4. **Concatenates content** - Combines them into standard format: `@php frontmatter @endphp view_content`
5. **Merges JavaScript** ✨ **NEW** - Combines dedicated JS file with any `<script>` tags from view
6. **Uses standard pipeline** - Continues with normal parsing and compilation process
7. **Generates same output** - Creates identical class and view files as single-file components

This approach provides **separation of concerns** while maintaining full compatibility with all existing features (computed properties, layouts, class-level attributes, etc.). The optional `.js` file enables **pure JavaScript development** without mixing code in Blade templates.

##### **Combined Script Output**

When both dedicated JS file and Blade `<script>` tags exist, they are merged:

**Multi-File Structure:**
```
counter/
├── counter.livewire.php
├── counter.blade.php       <!-- Contains <script>alert('from blade')</script> -->
└── counter.js              <!-- Contains console.log('from js file') -->
```

**Final Compiled Script** (`storage/.../scripts/counter_abc123.js`):
```javascript
// Script extracted from component
console.log('from js file')

// Script extracted from component
alert('from blade')
```

**Compilation Priority:**
1. **Dedicated JS file content** (if exists)
2. **Blade `<script>` tag content** (if exists)
3. **Combined into single compiled script file**

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

### Cache System ✨ **OPTIMIZED**

The compiler implements a hyper-efficient caching system:

- **Cache Directory**: `storage/framework/views/livewire/`
  - `/classes/` - Compiled PHP class files
  - `/views/` - Cleaned Blade view files
  - `/scripts/` - Extracted JavaScript files
  - `/metadata/` - Compilation metadata for cache validation
- **Filemtime-based invalidation** - Uses file modification times for efficient cache validation
- **In-memory cache** - Same-request optimization prevents redundant filesystem operations
- **Metadata-driven cache hits** - True cache hits avoid re-parsing components entirely
- **Automatic directory creation** - Cache directories are created as needed

#### **Cache Performance Improvements**
- ✅ **No file reading for cache validation** - Uses filemtimes instead of content hashing
- ✅ **True cache hits** - Metadata files store compilation results for instant reconstruction
- ✅ **In-memory optimization** - Same-request cache prevents duplicate work
- ✅ **Minimal filesystem operations** - Files read only once during compilation
- ✅ **Path-based file naming** - Consistent filenames prevent orphaned compiled files
- ✅ **File overwriting not duplication** - Source changes overwrite existing compiled files
- ✅ **Blade-like caching strategy** - Uses path hashing for stable compiled file locations
- ✅ **Integrated cache clearing** - Hooks into Laravel's `view:clear` command automatically
- ✅ **Direct file path rendering** - Bypasses view namespace lookup to prevent double compilation

#### **Integrated Cache Management**

The V4 compiler automatically integrates with Laravel's cache clearing commands:

```bash
# This now clears both Laravel views AND Livewire compiled files
php artisan view:clear

# Output:
# Compiled views cleared successfully.
# Livewire compiled files cleared (15 files removed).
```

**What gets cleared:**
- `/storage/framework/views/livewire/classes/` - Compiled PHP classes
- `/storage/framework/views/livewire/views/` - Cleaned Blade views  
- `/storage/framework/views/livewire/scripts/` - Extracted JavaScript files
- `/storage/framework/views/livewire/metadata/` - Compilation metadata

**Dedicated command still available:**
```bash
php artisan livewire:clear  # Clears only Livewire files + runs view:clear
```

#### **Critical Performance Fix: Direct File Path Rendering** ⚡

**Problem:** Original V4 implementation used Laravel's view namespace system (`view('livewire-compiled::component')`), causing Laravel to look up and compile view files on every request, even with Livewire cache hits.

**Solution:** Generated components now use direct file path rendering (`app('view')->file('/path/to/file.blade.php')`) which bypasses namespace lookup and only compiles the Blade template when the actual Blade cache is invalid.

**Before (Double Compilation):**
```php
// Generated render method:
return view('livewire-compiled::counter_abc123');
// ↓ Laravel namespace lookup
// ↓ Finds: storage/framework/views/livewire/views/counter_abc123.blade.php  
// ↓ Blade compiler runs on every request!
```

**After (Single Compilation):**
```php
// Generated render method:
return app('view')->file('/absolute/path/counter_abc123.blade.php');
// ↓ Direct file reference
// ↓ Laravel's normal Blade caching applies
// ↓ Only compiles when Blade cache is actually invalid!
```

**Performance Impact:**
- ✅ **Eliminates double compilation** - Leverages Laravel's native Blade caching
- ✅ **Proper Blade compilation** - `{{ $count }}` expressions work correctly
- ✅ **True cache efficiency** - Both Livewire AND Blade caching work together
- ✅ **Best of both worlds** - Pre-processing + native Blade compilation

This fix resolves the issue where profilers showed unnecessary Blade compilation activity even on cached components, while maintaining full Blade syntax support.

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
- Tests for parsing, compilation, caching, layout, script extraction, computed properties (including in islands), and error scenarios

#### ✅ **Layout Directive Support**
- Parses `@layout()` directives from component frontmatter
- Compiles to `#[Layout]` attributes on generated classes
- Supports both simple layouts and layouts with data arrays
- Works with both inline and external components

#### ✅ **Unified Script Extraction and Separation** ✨ **NEW**
- **Always extracts** `<script>` tags from all component views during parsing
- **Unified handling** for both single-file and multi-file components
- **Separates** scripts into dedicated `.js` files in compiled directory
- **Generates** clean view files with scripts completely removed
- **ES6 Import Hoisting**: Automatically hoists import statements to module top-level
- **Export Default Wrapping**: Wraps remaining code in `export default function run()` pattern
- **Runtime Access**: Generates `jsModuleSource()` and `jsModuleModifiedTime()` methods
- **Foundation** for future dynamic import and module loading features

#### ✅ **Runtime Script Access** ✨ **NEW**
- **File-based access** to script content via `jsModuleSource()` method
- **Minimal file I/O** - reads script content only when method is called
- **Protected methods** for internal use and safe access patterns
- **Error handling** with clear exceptions for missing script files
- **Modification time** `jsModuleModifiedTime()` method for cache headers and ETags
- **Consistent approach** both methods check file existence and handle errors gracefully

#### ❌ **Naked Script Transformation** (Removed)
- **REPLACED** by unified script extraction approach
- Previously wrapped scripts with `@script`/`@endscript` directives during compilation
- **NEW APPROACH**: All components now extract scripts into separate `.js` files
- **Cleaner separation**: Scripts and views are completely separated
- **Better performance**: No legacy directive processing needed
- **Unified handling**: Same approach for single-file and multi-file components

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
- **Dedicated JavaScript files** ✨ **NEW**: Optional `.js` files alongside `.livewire.php` and `.blade.php`
- **Script merging**: Combines dedicated JS files with any `<script>` tags from Blade views
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
- Script extraction and separation (🆕 NEW - unified approach)
- ES6 import hoisting and module wrapping (🆕 NEW)
- ❌ Legacy naked script transformation tests (expected failures - behavior changed)
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

### Script Extraction System ✨ **NEW**

The V4 compiler now includes a sophisticated script extraction system that separates JavaScript from component views.

#### **Script Extraction Process**

```php
// During parsing (parseComponent method):
$scripts = $this->extractScripts($viewContent);           // Extract scripts
$cleanViewContent = $this->removeScripts($viewContent);   // Clean view content

$parsed = new ParsedComponent(
    $frontmatter,
    $cleanViewContent,  // Scripts removed
    $isExternal,
    $externalClass,
    $layoutTemplate,
    $layoutData,
    $scripts           // Scripts stored separately
);
```

#### **Generated File Structure**

The new system generates an additional file type:

```
storage/framework/views/livewire/
├── classes/              (existing - compiled PHP classes)
├── views/                (existing - cleaned view files)
└── scripts/              (🆕 NEW - extracted JavaScript files)
    └── ComponentName_hash123.js
```

#### **Script Data Structure**

Each extracted script contains structured information:

```php
[
    'content' => 'console.log("Script content");',
    'attributes' => [
        'type' => 'text/javascript',
        'defer' => true,
        'async' => false,
    ],
    'fullTag' => '<script type="text/javascript" defer>...</script>'
]
```

#### **CompilationResult Enhancement**

The `CompilationResult` class now includes script file tracking:

```php
class CompilationResult {
    public string $className;
    public string $classPath;
    public string $viewName;
    public string $viewPath;
    public ?string $scriptPath;  // 🆕 NEW

    public function hasScripts(): bool  // 🆕 NEW
}
```

**Generated classes with scripts also include:**
```php
class GeneratedComponent extends \Livewire\Component {
    // ... component logic ...

    protected function jsModuleSource(): string  // 🆕 NEW
    {
        $scriptPath = '/path/to/compiled/script.js';
        if (! file_exists($scriptPath)) {
            throw new \RuntimeException("Script file not found: [{$scriptPath}]");
        }
        return file_get_contents($scriptPath);
    }

    protected function jsModuleModifiedTime(): int  // 🆕 NEW
    {
        $scriptPath = '/path/to/compiled/script.js';
        return file_exists($scriptPath) ? filemtime($scriptPath) : filemtime(__FILE__);
    }
}
```

#### **Future Integration Points**

This foundation enables future enhancements:

- **Dynamic ES6 imports**: `import('./path/to/script.js')`
- **Module loading strategies**: Lazy loading, preloading
- **Build tool integration**: Minification, bundling
- **Asset pipeline**: Automatic versioning, cache busting
- **Runtime script access**: File-based loading with proper error handling
- **Component-specific modules**: Self-contained JavaScript per component
- **Conditional loading**: Load scripts only when component is rendered
- **HTTP caching**: ETags, Last-Modified headers, and 304 responses
- **CDN integration**: Cache invalidation based on modification times
- **Progressive loading**: Smart caching strategies for component scripts
- **Post-processing**: Scripts can be modified/minified after compilation
- **Development tools**: Hot reloading and script file watching

#### **Legacy Compatibility**

Components with existing `@script` directives are unchanged:
- Script extraction **skipped** if `@script` directives detected
- Maintains backward compatibility
- Gradual migration path available

#### **Example: Script Extraction in Action**

**Input Component** (`counter.livewire.php`):
```php
@php
new class extends Livewire\Component {
    public $count = 0;
    public function increment() { $this->count++; }
}
@endphp

<div>
    <h1>Count: {{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>

<script>
console.log('Counter component loaded!');

document.addEventListener('DOMContentLoaded', function() {
    console.log('Counter ready:', {{ $count }});
});
</script>
```

**Generated Files:**

1. **Compiled View** (`storage/.../views/counter_abc123.blade.php`):
```html
<div>
    <h1>Count: {{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
```

2. **Extracted Script** (`storage/.../scripts/counter_abc123.js`):
```javascript
export default function run() {
    // Script section 1
    console.log('Counter component loaded!');

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Counter ready:', {{ $count }});
    });
}
```

**Key Changes:**
- ✅ **All scripts extracted** - No longer dependent on ES6 imports
- ✅ **Clean view separation** - Views contain zero JavaScript code
- ✅ **Consistent wrapping** - All scripts wrapped in `export default function run()`
- ✅ **Dynamic import ready** - Can be loaded via `import()` from Livewire runtime

#### **Example: ES6 Import Hoisting** ✨ **NEW**

**Input Component with Imports** (`animated-counter.livewire.php`):
```php
@php
new class extends Livewire\Component {
    public $count = 0;
    public function increment() { $this->count++; }
}
@endphp

<div>
    <h1>Count: {{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>

<script>
import { animate } from 'https://cdn.jsdelivr.net/npm/animejs/+esm';
import { debounce } from 'https://cdn.skypack.dev/lodash-es';

console.log('Animated counter loading...');

const button = document.querySelector('button');
const counter = document.querySelector('h1');

const debouncedAnimate = debounce(() => {
    animate({
        targets: counter,
        scale: [1, 1.2, 1],
        duration: 300,
        easing: 'easeInOutQuad'
    });
}, 100);

button.addEventListener('click', debouncedAnimate);
</script>
```

**Generated Script** (`storage/.../scripts/animated-counter_def456.js`):
```javascript
// Hoisted imports
import { animate } from 'https://cdn.jsdelivr.net/npm/animejs/+esm';
import { debounce } from 'https://cdn.skypack.dev/lodash-es';

export default function run() {
    // Script section 1
    console.log('Animated counter loading...');

    const button = document.querySelector('button');
    const counter = document.querySelector('h1');

    const debouncedAnimate = debounce(() => {
        animate({
            targets: counter,
            scale: [1, 1.2, 1],
            duration: 300,
            easing: 'easeInOutQuad'
        });
    }, 100);

    button.addEventListener('click', debouncedAnimate);
}
```

**Runtime Usage with Dynamic Imports:**
```javascript
// Livewire runtime code
async function loadComponentScript(componentClass) {
    try {
        // Get script content via runtime method
        const scriptContent = componentClass.jsModuleSource();

        // Create a blob URL for the module
        const blob = new Blob([scriptContent], { type: 'application/javascript' });
        const moduleUrl = URL.createObjectURL(blob);

        // Dynamic import the module
        const module = await import(moduleUrl);

        // Call the run function with component context
        const runFunction = module.default;
        runFunction.call(this); // 'this' is the Livewire component instance

        // Clean up the blob URL
        URL.revokeObjectURL(moduleUrl);

    } catch (error) {
        console.error('Failed to load component script:', error);
    }
}
```