## Livewire component registry system

This is the system that Livewire will use to resolve an instantiable component by name.

For example:

```php
// When rendering tags...
<livewire:some-component />

// When manually newing up a component:
Livewire::new('some-component');

// When mounting a component manually:
Livewire::mount('some-component', ['some' => 'params']);
```

Somehow, interntally, Livewire has to convert the name `some-component` into an actual Livewire component instance that can be instantiated, mounted, and rendered.

There are two sides to this registry system:
- Registration
- Resolution

Let's describe these systems how they currently behave Livewire V3 and are interected with and are implemented...

## V3 system

### Registration

Let's walk through how components are currently registered in V3:

First, you can explictly register them like so:

```php
// Register a one-off component with a name alias...
Livewire::component('some-component', SomeComponent::class);

// Register a one-off component as a standalone class...
Livewire::component(SomeComponent::class);

// Register a callback to resolve a component by name if the resolution system can't find one...
Livewire::resolveMissingComponent(function ($nameOrClass) {
    return SomeClass::class;
});
```

The above two affordances are explicit mappings between names and classes.

The rest of the system uses a dynamic resolution strategy that converts a name such as some-component into a class name like SomeClass and looks for it in the configured class namespace. (App\Livewire by default)

The user can customize this configured namespace in their config file like so:

```php
// Path: config/livewire.php

'class_namespace' => 'CustomApp\\Livewire',
```

### Resolution

The entire Livewire component registry system is isolated to the following file: src/Mechanisms/ComponentRegistry.php

Here are the public methods that are consumed by the rest of the application:

```php
// Currently, Livewire components can be referenced by a simple name like some-component, or you might want to reference it by it's actuall class like App\Livewire\SomeComponent, or you might even have an instance of the component class itself. In all these cases, you may want to resolve the component's "name". The simple string that can be used inside a component tag (livewire:some-component) or passed to the browser to avoid exposing your app class structure, etc. This method is used heavily inside tests and also inside a few other systems as well...
app(ComponentRegistry::class)->getName($nameOrClassOrComponent);

// This is the main method that converts the aforementioned name into an actual component instance. The component instance is just the instantiated component class with a random ID assigned. You can control the ID instead of letting it be randomly generated - this is used when re-hydrating a component so that you can new it up with it's assigned ID from the last request...
app(ComponentRegistry::class)->new($nameOrClass, $id = null);

// This is a way for third party libraries and such to have one last chance to resolve a component name into an actual class. It's sort of a Just In Time way to register component's...
app(ComponentRegistry::class)->resolveMissingComponent(function ($nameOrClass) {
    return SomeClass::class;
});

// This is mostly used by the testing system to determine if a name or a class is something that actually points to a Livewire component in the registry system. It really should just be called ->isRegistered(). That would be a more descriptive name. It does this check by taking the input and converting it into a class and then back into a name. if the input passes through unscathed we can determine that it's registered. Kind of a clever implementation I'd say to re-use important code paths in the registration system.
app(ComponentRegistry::class)->isDiscoverable($classOrName);
```

When resolving a new component, the system works like this:

- Resolve a component's class/name pair
- new up the class
- set it's ID
- set it's name
- return the instance

Let's discuss that first step in depth, that's the actual important part of the system:

Every component in the current system must have two identifiable parts: a name (the simple string you see referenced with dot notation in component tags and such), and a class (the actually FQCN for the component class).

These two parts must be deterministically paired in this way: you should be able to determine a class by a name, and a name by a class.

This way you can do things like `Livewire::test(App\Livewire\SomeComponent::class);` and also `<livewire:some-component />`.

All that logic is handled by the very important method: `getNameAndClass()`. That's the real system, here's how it works:

A) Check if what was passed in is a simple class or object, in that case skip to Step C)
B) If not a class or object, we can assume it's a name (like some-component). Let's try to get it into a class
    B.1) Check the list of name aliases for a match and return the class.
    B.2) Check the list of non-aliased classes (one-off class registrations without a name provide like this: `Livewire::component(SomeClass::class)`)
    B.3) Generate a fresh class from a name
        B.3.1) Get the configured root namespace
        B.3.2) Convert a name like foo.bar-baz into Foo\BarBaz
        B.3.3) Concat them into: App\Livewire\Foo\BarBaz
    B.4) If that class doesn't actually exist, run the resolveMissingComponents callbacks to give plugins and such an opportunity to resolve component names themselves...
C) Check that the class exists and is a subclass of Livewire\Component. If not, we bail with a ComponentNotFound error
D) Generate a name from the class
    D.1) Look for the class in the aliases property and return the key (name)
    D.2) Check the list of one-off class registrations (or non-aliased classes) and return the hash of the class name (what we use as a deterministically generated name for those components)
    D.3) If those aren't found, generate a fresh name from the class
       D.3.1) This implementation is kinda complicated. Just reference the `generateNameFromClass` method rather than me describing a million odd little steps here
E) Return the name and class pair.

By the time this system is complete, if it doesn't error out with ComponentNotFound we know we have a usable class and name that can be used interchangebly to instantiate a new component in the system.

Ok, now let's look at what we want to enable in V4 and what will need to be changed.

## V4 system

As you can see, the V3 system is class-based. Names get converted to classes and classes get converted to names. When a name needs to be auto-discovered, it looks in a PHP namespace.

This makes sense because V3 Livewire all starts with Livewire classes which have a render method which determin the component's view at runtime.

This is fundamentally different than how I'd like V4 to work.

I want V4 to be a view-first system. Where names map to Blade views, that have a component class definied inline in the front-matter (like Volt) or that reference external classes in the front-matter.

For example, here's my vision for a generic `some-component` in V4:

It would be rendered inside some other blade file like so:

```php
<livewire:some-component />
```

Then here would be the component source file:

```php
// Path resources/views/components/some-component.blade.php

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

Alternatively, this file could reference an external class like so:

```php
// Path resources/views/components/some-component.blade.php

@php(new App\Livewire\SomeComponent::class)

<div>
    Count: {{ $count }}

    <button wire:click="increment">Increment</button>
</div>
```

Then the App\Livewire\SomeComponent.php class would have the normal Livewire component class contents like V3 (except it wouldn't have a render() method because now the view is the source of truth and can't be changed at runtime you know)

Here are the advantages to this approach:
* Keeps an entire Livewire class from soup to nuts in one file
* If people prefer the old way they can still use that with the external reference syntax at the top. so best of both worlds
* It allows us to know the view at compile-time to make compile-time optimizations like code-folding. Where right now we don't know anything about a Livewire component's view at compile-time
* It gives users a more clear mapping to component names to views/components directory
* Is much more inline with anonymous Blade components which are overwhelmingly popular you know?

Also now that we are going view-first it makes sense to add namespaces to livewire components like Vue has.

I would like to be able to do this:

```php
Livewire::namespace('foo', __DIR__ . '/../stubs/resources/views');
```

then reference it like so in a template:

```php
<livewire:foo::some-component />
```

Also we will need to support pointing a component registration name to a view name instead of a class like it is right now:

```php
Livewire:component('some-component', __DIR__ . '/../views/some-component.blade.php');
```

But we should probably support both for backwards compatibility.

## Big question: Backwards compatibility

* Do we still allow someone to register a class-first component? In that case we'd have the problem of not knowing the view name at compile-time in case we do compile-time optimizations
* Do we instead force everyone into this new paradaigm?
* Do we just punt on breaking things and instead compile these components into classes/views and have them fit neatly inside the current registry system but as a layer on top or something?
* How does this change the system need to change to support all of this?

## Current V4 Implementation Status

### What's Been Implemented

The first piece of the V4 registry system has been implemented: the **ComponentViewPathResolver**. This class handles the view-first component resolution aspect of the new system.

### File Structure

The V4 registry system is organized in:

```
src/V4/Registry/
├── ComponentViewPathResolver.php
├── ComponentViewPathResolverUnitTest.php
└── Exceptions/
    └── ViewNotFoundException.php
```

This structure keeps the registry system separate from future V4 features.

### ComponentViewPathResolver Implementation

**Location**: `src/V4/Registry/ComponentViewPathResolver.php`
**Namespace**: `Livewire\V4\Registry`

The `ComponentViewPathResolver` class extends `Livewire\Mechanisms\Mechanism` and provides the foundation for view-first component resolution. It has three main public methods:

```php
// Register a component name to a specific view file path
$resolver->component('custom-component', '/path/to/custom-view.blade.php');

// Register a namespace that maps to a directory of views
$resolver->namespace('admin', resource_path('views/admin'));

// Resolve a component name to its view file path
$viewPath = $resolver->resolve('some-component');
```

### Key Features Implemented

#### 1. **Alias Registration**
Components can be explicitly registered with custom view paths:
```php
$resolver->component('custom-button', '/path/to/button-view.blade.php');
```

#### 2. **Namespace Support**
Supports Vue-style namespacing:
```php
$resolver->namespace('admin', resource_path('views/admin'));
// Now 'admin::user-list' resolves to resources/views/admin/user-list.blade.php
```

#### 3. **Four File Resolution Conventions**
When resolving a component name, the system tries these conventions in order:

1. **Direct file**: `foo.livewire.php`
2. **Multi-file component**: `foo/` directory containing both `foo.livewire.php` and `foo.blade.php`
3. **Folder with same name**: `foo/foo.livewire.php`
4. **Index file**: `foo/index.livewire.php`

#### 4. **Multi-File Component Support** ✨ **NEW**
The resolver now supports directory-based multi-file components where the frontmatter and view content are separated into individual files:

**Directory Structure:**
```
counter/
├── counter.livewire.php  (frontmatter/class definition)
└── counter.blade.php     (view content)
```

This approach provides:
- **Separation of concerns** - PHP logic separate from HTML markup
- **Better IDE support** - Proper syntax highlighting for both PHP and Blade
- **Team collaboration** - Developers can work on logic and markup independently
- **Full feature compatibility** - All existing features work (computed properties, layouts, etc.)

#### 5. **Nested Component Support**
Supports dot notation for nested components:
```php
// 'forms.input' resolves to forms/input.blade.php
$resolver->resolve('forms.input');
```

#### 6. **Multiple Directory Fallback**
Tries multiple default directories:
- `resources/views/components` (first priority)
- `resources/views/livewire` (fallback)

#### 7. **Priority System**
Aliases take priority over default resolution, so explicit registrations override auto-discovery.

### Resolution Algorithm

The resolution process works as follows:

1. **Check aliases first** - If the component name is explicitly registered, use that path
2. **Handle namespaced components** - Parse `namespace::component` syntax and resolve within the namespace directory
3. **Try default directories** - Look in `resources/views/components` then `resources/views/livewire`
4. **Apply file conventions** - For each directory, try the four file naming conventions:
   - Direct single file (`foo.livewire.php`)
   - Multi-file directory (`foo/` with both `foo.livewire.php` and `foo.blade.php`)
   - Subdirectory single file (`foo/foo.livewire.php`)
   - Index file (`foo/index.livewire.php`)
5. **Return path type** - Returns either:
   - **File path** for single-file components → passed to `SingleFileComponentCompiler::compile()`
   - **Directory path** for multi-file components → passed to `SingleFileComponentCompiler::compileMultiFileComponent()`
6. **Throw exception** - If no view file is found, throw `ViewNotFoundException`

### Integration with Compilation System

The resolver integrates seamlessly with the V4 compilation system in `IntegrateV4.php`:

```php
// Registry resolves component name to either file or directory path
$viewPath = $this->finder->resolve($componentName);

// Compilation system detects path type and uses appropriate compiler method
if (is_dir($viewPath)) {
    // Multi-file component - directory compilation
    $result = $this->compiler->compileMultiFileComponent($viewPath);
} else {
    // Single-file component - standard compilation
    $result = $this->compiler->compile($viewPath);
}
```

This unified approach means both component types generate identical compiled output and have the same runtime performance.

### Testing

Comprehensive unit tests cover:
- ✅ Alias registration and resolution
- ✅ Namespace registration and resolution
- ✅ All four file resolution conventions (including multi-file components)
- ✅ Multi-file component directory validation
- ✅ Nested components with dot notation
- ✅ Directory fallback behavior
- ✅ Exception handling for missing files/namespaces
- ✅ Path normalization
- ✅ Priority system (aliases override defaults)
- ✅ Multi-file component resolution (NEW)

**Test Command**: `phpunit src/V4/Registry/ComponentViewPathResolverUnitTest.php`

### Example Usage

```php
use Livewire\V4\Registry\ComponentViewPathResolver;

$resolver = new ComponentViewPathResolver();

// Register a custom component
$resolver->component('special-button', resource_path('views/custom/button.livewire.php'));

// Register an admin namespace
$resolver->namespace('admin', resource_path('views/admin'));

// Resolve various component types
$path1 = $resolver->resolve('some-component');        // → resources/views/components/some-component.livewire.php
$path2 = $resolver->resolve('admin::user-list');     // → resources/views/admin/user-list.livewire.php
$path3 = $resolver->resolve('forms.input');          // → resources/views/components/forms/input.livewire.php
$path4 = $resolver->resolve('special-button');       // → resources/views/custom/button.livewire.php

// Multi-file component resolution (NEW)
$path5 = $resolver->resolve('counter');              // → resources/views/components/counter/ (directory)
```

#### Component Structure Examples

**Single-File Component:**
```
resources/views/components/
└── counter.livewire.php
```

**Multi-File Component:**
```
resources/views/components/
└── counter/
    ├── counter.livewire.php
    └── counter.blade.php
```

**Multi-File Component Files:**

`counter/counter.livewire.php`:
```php
use Livewire\Attributes\Computed;

new class extends Livewire\Component {
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

`counter/counter.blade.php`:
```html
<div>
    <h2>Count: {{ $count }}</h2>
    <p>Double: {{ $doubleCount }}</p>
    <button wire:click="increment">+</button>
</div>
```

### What Still Needs to Be Done

This implementation addresses the **view resolution** portion of the V4 registry system and now includes **complete multi-file component support** with **full compilation system integration**. However, several pieces are still needed to complete the V4 vision:

#### ✅ **COMPLETED: Multi-File Component Support**
- ✅ Component resolution for directory-based multi-file components
- ✅ Integration with `SingleFileComponentCompiler::compileMultiFileComponent()`
- ✅ Automatic detection and routing of file vs. directory paths
- ✅ Full feature compatibility with single-file components

#### 1. **View Parser** (Partially Complete)
The `SingleFileComponentCompiler` handles parsing, but this could be further abstracted:
- ✅ Parse `@php` blocks and extract component class definitions
- ✅ Handle both inline anonymous classes and external class references
- ✅ Support for multi-file component concatenation
- ⚠️ Could benefit from more sophisticated parsing for complex scenarios

#### 2. **Component Class Extraction** (Complete)
✅ Handle both inline anonymous classes and external class references:
```php
// Inline class extraction (DONE)
@php
new class extends Livewire\Component {
    // ... component logic
}
@endphp

// External class reference extraction (DONE)
@php(new App\Livewire\SomeComponent::class)
```

#### 3. **Integration with V3 Registry** (Partially Complete)
- ✅ `ComponentViewPathResolver` integrated with compilation system
- ✅ View-first components work alongside class-first components
- ⚠️ Full backward compatibility testing needed

#### 4. **Component Instantiation** (Complete)
✅ Bridge between resolved view paths and component instances via compilation system

#### 5. **Backwards Compatibility Layer**
Determine how V3 class-first components will coexist with V4 view-first components.

#### 6. **Configuration System**
Allow users to configure default view directories and other registry settings.

### Next Steps

With **multi-file component support now complete**, the V4 registry system has achieved significant functionality. The immediate priorities are:

#### 1. **Comprehensive Backwards Compatibility Testing**
- Test V3 class-first components alongside V4 view-first components
- Ensure existing applications can gradually migrate to V4 without breaking changes
- Document migration patterns and best practices

#### 2. **Configuration System Enhancement**
- Allow users to configure default view directories
- Support custom file extensions beyond `.livewire.php`
- Provide registry configuration options

#### 3. **Advanced Parser Improvements**
While the current `SingleFileComponentCompiler` handles most scenarios well, it could benefit from:
- More sophisticated PHP parsing for edge cases
- Better error messages for malformed components
- Support for additional frontmatter formats

#### 4. **Developer Experience Improvements**
- Enhanced error messages with suggestions
- Better debugging tools for component resolution
- IDE integration and tooling support

#### 5. **Performance Optimizations**
- Caching for repeated component resolutions
- Optimized file system operations
- Benchmark and optimize the resolution algorithm

The **core view-first component system is now fully functional** with both single-file and multi-file component support, making V4 ready for real-world usage and testing.
