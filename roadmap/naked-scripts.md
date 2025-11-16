## Naked scripts

In Livewire v3, Livewire allows you to run javascript code right from your blade view like so:

```php
<div>
    // ...
</div>

@script
<script>
    //
</script>
@endscript
```

Livewire will pick up @script and treat it differently than a normal script tag - it will give it special scope access to things like `$wire` and it also will run it at the right point in execution so you don't have to run event listeners like: livewire:init or DOMContenetLoaded.

However, I find the `@script` thing to be unergonomic.

In the new .wire.php components (single file components), I'd like to not need @script and just detect if there is a dangling script element below the main root element of the component.

So i want something like this:

```php
<div>
    // ...
</div>

<script>
    //
</script>
```

### Implementation

For now the easiest thing is to tackle this at the compiler level and just compile the single file component's view portion to something with @script wrapping the script element.

So let's do that.

## Current V4 Implementation Status

### ✅ **Feature Complete**

The naked script transformation feature has been fully implemented in the V4 single-file component compiler.

### How It Works

The compiler automatically detects naked `<script>` tags in single-file components and wraps them with `@script`/`@endscript` directives during compilation.

#### Input (Single-File Component):
```php
{{-- resources/views/components/counter.blade.php --}}
@php
new class extends Livewire\Component {
    public $count = 0;

    public function increment() {
        $this->count++;
    }
}
@endphp

<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>

<script>
    console.log('Component loaded:', $wire.count);

    $wire.on('updated', () => {
        console.log('Count updated!');
    });
</script>
```

#### Compiled Output (Generated View):
```php
{{-- storage/framework/views/livewire/views/counter_abc123.blade.php --}}
<div>
    Count: {{ $count }}
    <button wire:click="increment">Increment</button>
</div>

@script
<script>
    console.log('Component loaded:', $wire.count);

    $wire.on('updated', () => {
        console.log('Count updated!');
    });
</script>
@endscript
```

### Features Implemented

#### ✅ **Automatic Script Wrapping**
- Detects naked `<script>` tags in view content
- Automatically wraps them with `@script`/`@endscript` directives
- Preserves all script attributes (type, defer, async, etc.)

#### ✅ **Intelligent Processing**
- Only processes components that don't already have `@script` directives
- Skips empty or whitespace-only script tags
- Handles multiple script tags in a single component

#### ✅ **Attribute Preservation**
- Maintains all script tag attributes:
```php
<script type="module" defer async>
    // Script content
</script>
```
Becomes:
```php
@script
<script type="module" defer async>
    // Script content
</script>
@endscript
```

#### ✅ **Safe Coexistence**
- Components with existing `@script` directives are left unchanged
- Mixed naked and wrapped scripts are not supported (to avoid confusion)

### Implementation Details

#### **Location**: `src/V4/Compiler/SingleFileComponentCompiler.php`

The transformation is handled by the `transformNakedScripts()` method, called during view compilation:

```php
protected function generateView(CompilationResult $result, ParsedComponent $parsed): void
{
    $processedViewContent = $this->transformNakedScripts($parsed->viewContent);
    File::put($result->viewPath, $processedViewContent);
}

protected function transformNakedScripts(string $viewContent): string
{
    // Don't process if there are no script tags
    if (!str_contains($viewContent, '<script')) {
        return $viewContent;
    }

    // Don't process if there are already @script directives present
    if (str_contains($viewContent, '@script')) {
        return $viewContent;
    }

    // Transform naked scripts into @script wrapped versions
    $pattern = '/<script\b[^>]*>(.*?)<\/script>/s';

    return preg_replace_callback($pattern, function ($matches) {
        $fullScriptTag = $matches[0];
        $scriptContent = $matches[1];

        // Skip empty scripts
        if (empty(trim($scriptContent))) {
            return $fullScriptTag;
        }

        // Wrap the script tag with @script directives
        return "\n@script\n" . $fullScriptTag . "\n@endscript\n";
    }, $viewContent);
}
```

### Testing

Comprehensive test coverage with 6 dedicated tests:

**Test Command**: `phpunit src/V4/Compiler/SingleFileComponentCompilerUnitTest.php --filter script`

#### Naked Script Tests (6 tests)
- ✅ **Basic transformation** - Wraps naked scripts with @script directives
- ✅ **Respects existing directives** - Leaves @script wrapped scripts unchanged
- ✅ **Multiple scripts support** - Handles multiple naked scripts in one component
- ✅ **Empty script handling** - Skips empty or whitespace-only scripts
- ✅ **Attribute preservation** - Maintains all script tag attributes
- ✅ **No-op when no scripts** - Does nothing when no scripts are present

### Usage Examples

#### Basic Usage
```php
{{-- Input: resources/views/components/my-component.blade.php --}}
@php
new class extends Livewire\Component {
    public $message = 'Hello';
}
@endphp

<div>{{ $message }}</div>

<script>
    console.log('Component ready');
</script>
```

#### Multiple Scripts
```php
{{-- Input: resources/views/components/complex-component.blade.php --}}
@php
new class extends Livewire\Component {
    // Component logic
}
@endphp

<div>Component content</div>

<script>
    // Main component script
    console.log('Main script');
</script>

<script type="module">
    // Module script
    import { helper } from './helper.js';
</script>
```

#### External Component with Scripts
```php
{{-- Input: resources/views/components/external-component.blade.php --}}
@php(new App\Livewire\ExternalComponent)

<div>External component content</div>

<script>
    // This will be wrapped automatically
    $wire.on('event', callback);
</script>
```

### Benefits

#### 1. **Improved Developer Experience**
- No need to remember `@script` directive syntax
- More intuitive - just write normal `<script>` tags
- Reduced boilerplate in component files

#### 2. **Automatic Livewire Integration**
- Scripts automatically get `$wire` scope access
- Proper execution timing without manual event listeners
- All Livewire script features work transparently

#### 3. **Backward Compatibility**
- Existing components with `@script` directives continue to work
- Gradual migration path for existing codebases

#### 4. **Performance**
- Transformation happens at compile time, not runtime
- No performance impact on component rendering
- Cached compilation results

### Next Steps

The naked script feature is complete and ready for use. Future enhancements could include:

#### 1. **Enhanced Processing**
- Support for mixed naked and wrapped scripts in the same component
- More sophisticated script detection (avoiding scripts inside comments, etc.)

#### 2. **Development Tools**
- IDE support for naked script syntax highlighting
- Linting rules for naked script best practices

#### 3. **Advanced Features**
- Conditional script wrapping based on component metadata
- Custom script transformation rules

The naked script transformation provides a seamless, intuitive way to add JavaScript to Livewire components while maintaining all the benefits of the `@script` directive system.