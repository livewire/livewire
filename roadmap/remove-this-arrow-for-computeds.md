# Computed Properties in V4 Single-File Components

## Problem Statement

In Livewire V4 single-file components, we want developers to be able to use clean syntax like `{{ $computedProperty }}` instead of `{{ $this->computedProperty }}` in their views, while preserving the Just-In-Time (JIT) evaluation behavior that makes computed properties performant.

### Background Context

In Livewire, public properties are made available to views naturally:

```php
@php
new class extends Livewire\Component {
    public $foo = 'bar';
}
@endphp

<div>
    Foo: {{ $foo }}
</div>
```

In traditional V3 components, computed properties require `$this->` syntax in views because they need JIT evaluation:

```php
public function render()
{
    return view('livewire.something', [
        'computedValue' => $this->computedValue, // Manual passing
    ])
}
```

**V4 Challenge**: Single-file components don't have explicit `render()` methods, so we can't manually pass computed values to the view. This creates a syntax inconsistency where computed properties require `{{ $this->property }}` while regular properties use `{{ $property }}`.

## Solution Analysis

### Considered Approaches

**Option A: Lose JIT (Eager Evaluation)**
- ❌ Significant performance regression
- ❌ Breaks existing computed property contracts
- ❌ All computeds evaluated whether used or not

**Option B: PHP Lazy Variables**
- ❌ PHP doesn't support native lazy evaluation
- ❌ Complex implementation with closures/generators
- ❌ Poor integration with Blade syntax

**Option C: Simple Compilation Transform**
- ✅ Preserves JIT evaluation
- ✅ Clean developer syntax
- ❌ Potential naming conflicts

**Option D3: Enhanced Compilation (IMPLEMENTED)**
- ✅ Preserves JIT evaluation
- ✅ Clean developer syntax
- ✅ Smart conflict detection
- ✅ Surgical transformations only
- ✅ Follows existing compiler patterns

## Implemented Solution

### Core Implementation

**Location**: `src/V4/Compiler/SingleFileComponentCompiler.php`

The solution adds computed property transformation to the existing compiler pipeline. When generating view files, the compiler:

1. **Detects computed properties** using regex pattern matching on `#[Computed]` attributes
2. **Validates for conflicts** to prevent variable reassignment issues
3. **Transforms view references** from `$property` to `$this->property` surgically
4. **Preserves JIT behavior** by maintaining method call semantics

### Key Methods Added

```php
protected function transformComputedPropertyReferences(string $viewContent, string $frontmatter): string
protected function extractComputedPropertyNames(string $frontmatter): array
protected function validateNoComputedVariableReassignments(string $viewContent, array $computedProperties): void
protected function transformPropertyReferences(string $viewContent, string $propertyName): string
```

### Regex Pattern for Detection

```php
$pattern = '/#\[\s*(?:\\\\?Livewire\\\\Attributes\\\\)?Computed[^\]]*\]\s*(?:public|protected|private)?\s*function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/';
```

**Handles all syntax variations:**
- `#[Computed]`
- `#[Computed(cache: true)]`
- `#[\Livewire\Attributes\Computed]`
- `#[ Computed ]` (with spaces)
- All visibility modifiers (`public`, `protected`, `private`, none)

### Integration Point

The transformation integrates seamlessly into the existing compilation pipeline in the `generateView()` method:

```php
protected function generateView(CompilationResult $result, ParsedComponent $parsed): void
{
    $processedViewContent = $this->transformNakedScripts($parsed->viewContent);

    // Transform computed property references if this is an inline component
    if ($parsed->hasInlineClass()) {
        $processedViewContent = $this->transformComputedPropertyReferences($processedViewContent, $parsed->frontmatter);
    }

    File::put($result->viewPath, $processedViewContent);
}
```

## Developer Experience

### Input (What developers write):
```php
@php
new class extends Livewire\Component {
    public $count = 0;

    #[Computed]
    public function total() {
        return $this->count * 10;
    }

    #[Computed]
    public function isEmpty() {
        return $this->count === 0;
    }
}
@endphp

<div>
    Count: {{ $count }}
    Total: {{ $total }}
    @if($isEmpty)
        <p>No items</p>
    @endif
</div>
```

### Output (What compiler generates):
```html
<div>
    Count: {{ $count }}
    Total: {{ $this->total }}
    @if($this->isEmpty)
        <p>No items</p>
    @endif
</div>
```

## Safety Features

### Conflict Detection
Prevents variable reassignment that would break computed property access:

```php
// This throws CompilationException:
@php($total = "something else")
{{ $total }}
```

**Error message**: `Cannot reassign variable $total as it's reserved for the computed property 'total'. Use a different variable name in your view.`

### Word Boundary Preservation
Only transforms exact matches, preserving similar variable names:

```php
#[Computed]
public function foo() { return "foo"; }

// In view:
{{ $foo }}      → {{ $this->foo }}     ✅ Transformed
{{ $foobar }}   → {{ $foobar }}        ✅ Preserved
{{ $foo_bar }}  → {{ $foo_bar }}       ✅ Preserved
```

### External Component Safety
No transformation occurs in external component references:

```php
@php(new App\Livewire\ExternalComponent)

<div>
    {{ $computedProperty }} {{-- Stays unchanged --}}
</div>
```

## Test Coverage

**Location**: `src/V4/Compiler/SingleFileComponentCompilerUnitTest.php`

**Added 8 comprehensive tests with 29 assertions:**

1. `it_transforms_computed_property_references_in_view_content`
2. `it_handles_computed_properties_with_various_attribute_syntaxes`
3. `it_does_not_transform_computed_properties_in_external_components`
4. `it_preserves_word_boundaries_when_transforming_computed_properties`
5. `it_throws_exception_when_computed_property_is_reassigned_in_view`
6. `it_handles_computed_properties_with_different_visibility_modifiers`
7. `it_does_not_transform_non_computed_methods`
8. `it_handles_complex_view_scenarios_with_computed_properties`

**Total test suite**: 52 tests, 183 assertions (all passing)

## Architecture Benefits

### Preserves JIT Evaluation
- Computed properties are still called only when accessed
- No performance impact from eager evaluation
- Maintains existing computed property contracts

### Follows Existing Patterns
- Similar to `transformNakedScripts()` method
- Consistent with compiler's transformation approach
- Integrates cleanly into compilation pipeline

### Future-Proof Design
- Easy to extend for additional computed property features
- Surgical transformations minimize side effects
- Well-tested foundation for enhancements

## Performance Characteristics

- **Compilation overhead**: Minimal regex processing during build time
- **Runtime performance**: Zero overhead - generates standard `$this->method()` calls
- **Cache efficiency**: Transformations only occur during compilation, cached afterwards
- **JIT preservation**: Computed properties evaluate exactly when accessed

## Future Enhancement Opportunities

1. **Inheritance support** - Could be extended to scan parent classes via reflection if needed
2. **IDE integration** - Tooling could highlight transformed properties
3. **Debug tools** - Development aids to show transformation mappings
4. **Advanced patterns** - Support for complex computed property scenarios

## Technical Notes for Future Development

### Key Design Decisions
- **Regex over reflection**: Chosen for performance and architectural consistency
- **Validation-first approach**: Conflict detection prevents runtime errors
- **Inline-only transformation**: External components maintain referential integrity
- **Word boundary safety**: Prevents accidental transformations of similar names

### Integration Dependencies
- Requires `ParsedComponent::hasInlineClass()` method
- Relies on frontmatter extraction from `parseComponent()`
- Uses existing `CompilationException` for error reporting

### Maintenance Considerations
- Regex pattern may need updates for new computed attribute features
- Test suite should be extended if computed property functionality expands
- Error messages should remain developer-friendly and actionable

This implementation successfully bridges the gap between clean developer syntax and performant JIT evaluation, making V4 single-file components feel natural while maintaining the power of computed properties.
