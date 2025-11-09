# Documentation Guide for LLMs

This guide provides instructions for AI assistants on how to write and edit Livewire v4 documentation.

## Component Code Examples

All component examples use **single-file anonymous class format**:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Component;
use App\Models\Post;

new class extends Component {
    public $title = '';

    public function save()
    {
        Post::create(['title' => $this->title]);
        $this->redirect('/posts');
    }
};
?>

<form wire:submit="save">
    <input type="text" wire:model="title">
    <button type="submit">Save</button>
</form>
```

### Key Points

- Start with file path comment: `<?php // resources/views/components/⚡name.blade.php`
- Use `new class extends Component` (anonymous class)
- End with `};` (or `};?>` if Blade follows in same block)
- No `render()` method unless demonstrating lifecycle hooks
- Use `#[Computed]` properties for view data
- Blade accesses computed properties via `$this->`
- Imports ordered by line length descending

### File Paths

```php
// Simple components
<?php // resources/views/components/⚡todos.blade.php

// Nested/RESTful components
<?php // resources/views/components/post/⚡create.blade.php
<?php // resources/views/components/post/⚡edit.blade.php

// Pages
<?php // resources/views/pages/post/⚡create.blade.php
```

**When to add file path comments:**
- Real-world examples readers might replicate ✅
- Generic/abstract examples with no meaningful filename ❌

## Component Naming

### RESTful (CRUD operations)
- `post.create`, `post.edit`, `post.show`, `posts.index`
- Text references: "the `post.edit` component..."

### Simple (utilities/one-offs)
- `todos`, `counter`, `dashboard`, `cart`
- Text references: "the `todos` component..."

**Stay consistent:** Use the same component name throughout a doc page unless genuinely different components.

## Code Block Organization

**Prefer separate blocks** (better syntax highlighting):
```php
<?php // resources/views/components/⚡todos.blade.php

use Livewire\Component;

new class extends Component { };
```

```blade
<div>@foreach ($todos as $todo)...</div>
```

**Combine when needed:**
```php
<?php

new class extends Component { };
?>

<div>...</div>
```

## Classes That Stay Traditional

Do NOT use anonymous classes for:
- Form objects (`App\Livewire\Forms\*`)
- Test files (`Tests\*`)
- Service providers, middleware, traits (definitions only)

## Documentation Style

- Clear and concise
- Show, don't just tell (use code examples)
- Active voice ("Livewire provides..." not "You can...")
- No emojis unless requested
- No superlatives ("powerful", "amazing")
- Reference components by name in text: "`post.edit` component" not "UpdatePost component"

## Reference Sections

Attribute and directive documentation pages should include a **Reference** section at the bottom that provides technical details.

### When to Include

**Include reference sections for:**
- Attributes with parameters (e.g., `#[Layout]`, `#[Computed]`, `#[Url]`)
- Directives with modifiers or parameters

**Omit reference sections for:**
- Marker attributes with no parameters (e.g., `#[Locked]`, `#[Reactive]`, `#[Async]`)
- Extremely simple attributes where the main docs fully cover usage

### Structure

Place the reference section after "Learn more" or at the end of the page:

```markdown
## Reference

```php
#[AttributeName(
    type $param1,
    type $param2 = default,
)]
```

**`$param1`** (required)
- Description of what it does

**`$param2`** (optional)
- Description of what it does
- Default: `default_value`
```

### Guidelines

**Parameter Signatures:**
- Use actual PHP type hints (`string`, `array`, `bool`, `int`, `?string`, `mixed`)
- Break long signatures into multiple lines with one parameter per line
- Use trailing commas even for the last parameter (PHP 7.3+ allows this)
- This prevents horizontal scrolling in documentation

**Parameter Documentation:**
- Mark each parameter as **(required)** or **(optional)**
- Show default values for optional parameters
- Keep descriptions concise—focus on what, not why (that's covered in main docs)
- Remove inline examples from parameter descriptions (avoid "Example:" lines)

**Examples Subsection:**
- Only include for complex attributes with many options (e.g., `#[Computed]`)
- Omit for simple attributes—the main docs already show usage
- When included, use practical, real-world examples

**Anonymous Class Convention:**
- When showing class-level attributes in examples, use anonymous class syntax
- Place the attribute on the same line after `new` keyword:
  ```php
  new #[Layout('layouts::app')] class extends Component { }
  ```

**Avoid Legacy Patterns:**
- Don't document deprecated parameters (e.g., `isolate: false` deprecated in favor of `bundle: true`)
- Focus on current recommended approaches

## Testing Documentation

**Pest is the recommended testing framework** for Livewire 4. All testing examples should use Pest syntax unless specifically demonstrating PHPUnit compatibility.

### Pest Examples

```php
it('can create a post', function () {
    expect(Post::count())->toBe(0);

    Livewire::test('post.create')
        ->set('title', 'My new post')
        ->call('save');

    expect(Post::count())->toBe(1);
});
```

### PHPUnit Examples

Only show PHPUnit when:
- Demonstrating that both frameworks work
- In a dedicated "Using PHPUnit" section
- Converting legacy docs that were PHPUnit-only

### Testing Resources

Link to Pest documentation where appropriate:
- Installation: https://pestphp.com/docs/installation
- Browser testing: https://pestphp.com/docs/browser-testing
- Expectations: https://pestphp.com/docs/expectations
- Main site: https://pestphp.com/

## Layout File Format

All layout file examples must match the official `livewire.layout.stub` format:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
    </body>
</html>
```

**Key points:**
- Title uses `config('app.name')` not `'Page Title'`
- Include `@vite(['resources/css/app.css', 'resources/js/app.js'])` directive
- Both `@livewireStyles` and `@livewireScripts` present
- Standard meta tags for charset and viewport

## Terminology

Use consistent terminology throughout the documentation:

### Messages (not Commits)

**Correct:**
- "When a message is sent to the server..."
- "The message payload contains..."
- "Message interceptors allow you to..."

**Incorrect:**
- "When a commit is sent..." ❌
- "The commit payload..." ❌

**Exception:** The `$commit()` method name itself is correct (it's an alias for `$refresh()`)

### Memoization vs Caching

**For single-request persistence** (computed properties):
- Use "memoize" or "memoized"
- "Computed properties are memoized for the duration of the request"

**For cross-request persistence** (session, database):
- Use "cache" or "cached"
- "You can cache data across requests using Laravel's cache"

## JavaScript in Components

### Script Tags

**Single-file and multi-file components** use bare `<script>` tags:

```blade
<script>
    $wire.on('post-created', () => {
        // Handle event
    });
</script>
```

**Class-based components** need the `@script` directive:

```blade
@script
<script>
    $wire.on('post-created', () => {
        // Handle event
    });
</script>
@endscript
```

Always include a warning in JavaScript documentation clarifying when `@script` is needed.

### Component Scripts

When showing component scripts:
- Use `$wire` directly (it's available in the scope)
- Use `this.$js.methodName` for registering JavaScript methods
- Never use `@script` wrapper in single-file component examples

## Escaping @ Symbols in Blade Directive Docs

When documenting Blade directives, the `@` symbol needs special handling:

**In regular prose within backticks:**
```markdown
The `@persist` directive allows you to...
```
Works fine - no escaping needed.

**In tip/warning/info blocks within backticks:**
```markdown
> [!tip]
> The `@@persist` directive requires...
```
Use `@@` (double at sign) inside tip/warning/info blocks.

**In code blocks:**
```blade
@persist('player')
    <audio controls></audio>
@endpersist
```
Use single `@` in code blocks - they render literally.

## Callout Boxes

Use callouts to highlight important information:

### Tips (positive, helpful)

```markdown
> [!tip] Smoke tests provide huge value
> Tests like these provide enormous value as they require very little maintenance.
```

### Warnings (potential issues)

```markdown
> [!warning] Must teleport outside the component
> Livewire only supports teleporting HTML outside your components.
```

### Info (neutral context)

```markdown
> [!info] When to use browser tests
> Use browser tests for critical user flows and complex interactions.
```

**Guidelines:**
- Keep callout titles short (3-6 words)
- Use sentence case, not title case
- Don't overuse - reserve for genuinely important points
- Place near relevant content, not at page top

## Cross-References

Link to related documentation generously:

**Format:**
```markdown
For more information, see [URL feature](/docs/4.x/url).
Learn more about [lazy loading](/docs/4.x/lazy).
```

**Guidelines:**
- Use relative paths: `/docs/4.x/feature-name`
- Link text should be the feature name, not "click here"
- Reference attributes/directives by name: `#[Computed]` or `@persist`
- File path references use format: `file.php:123` for line numbers

## See Also Sections

Many documentation pages benefit from a "See also" section at the bottom that guides readers to related topics. This helps users discover connected features and understand how different parts of Livewire work together.

### When to Add

**Include "See also" sections for:**
- All Essentials pages (Components, Pages, Properties, Actions, Forms, Events, Lifecycle Hooks, Nesting, Testing)
- Most Features pages (Alpine, Navigate, Islands, Lazy Loading, Loading States, Validation, File Uploads, URL Query Parameters, Computed Properties, Redirecting, Pagination)
- Commonly-used directive pages (wire:model, wire:loading, wire:navigate, wire:click, wire:submit)
- Advanced topic pages (Morphing, Hydration, JavaScript)

**Skip "See also" sections for:**
- Very simple or specialized features (e.g., Teleport)
- Attribute/directive reference pages (these are narrowly focused)
- Pages that are already very short

### Format

Place the section at the very bottom of the page:

```markdown
## See also

- **[Page Title](/docs/4.x/url)** — Brief description of how it relates
- **[Another Page](/docs/4.x/url)** — Why this is relevant to the current topic
- **[Third Page](/docs/4.x/url)** — What the user will learn there
```

### Guidelines

- **Keep it concise:** 3-5 links maximum
- **Focus on direct relationships:** Only link to closely related topics, not everything tangentially related
- **Use consistent format:** Bold link text, em dash, brief description
- **Order by relevance:** Most related topics first
- **Brief descriptions:** One line explaining the connection (not full feature descriptions)
- **Active voice in descriptions when natural**

### Examples by Page Type

**For foundational pages (e.g., Properties):**
```markdown
## See also

- **[Forms](/docs/4.x/forms)** — Bind properties to form inputs with wire:model
- **[Computed Properties](/docs/4.x/computed-properties)** — Create derived values with automatic memoization
- **[Validation](/docs/4.x/validation)** — Validate property values before persisting
- **[Locked Attribute](/docs/4.x/attribute-locked)** — Prevent properties from being manipulated client-side
```

**For feature pages (e.g., Islands):**
```markdown
## See also

- **[Nesting](/docs/4.x/nesting)** — Alternative approach using child components
- **[Lazy Loading](/docs/4.x/lazy)** — Defer loading of expensive content
- **[Computed Properties](/docs/4.x/computed-properties)** — Optimize island performance with memoization
```

**For directive pages (e.g., wire:model):**
```markdown
## See also

- **[Forms](/docs/4.x/forms)** — Complete guide to building forms with Livewire
- **[Properties](/docs/4.x/properties)** — Understand data binding and property management
- **[Validation](/docs/4.x/validation)** — Validate bound properties in real-time
```

### Link Types to Include

Prioritize these relationship types:
1. **Prerequisites** - Concepts users should understand first
2. **Common combinations** - Features often used together
3. **Alternatives** - Different approaches to similar problems
4. **Deep dives** - More detailed coverage of mentioned topics
5. **Related attributes/directives** - PHP attributes or Blade directives for the feature

### Maintaining Bidirectional Links

When adding a "See also" section:
- Consider whether the linked pages should also link back
- Keep relationships symmetrical where appropriate
- A linking to B doesn't always mean B should link to A (depend on relevance)

## Page Organization

### Tutorial Pages (Features)

Structure learning-focused pages with clear progression:

1. **Introduction** - What it is, why use it
2. **Basic usage** - Simplest example
3. **Essential concepts** - Most common patterns first
4. **Advanced features** - Progressive disclosure
5. **Common patterns** - Real-world examples (optional)
6. **Best practices** - Performance, debugging tips (optional)
7. **Reference** - API details, parameters, edge cases

**Move reference material to the bottom.** Users learning a feature don't need exhaustive API details up front.

### Reference Pages (Attributes/Directives)

Structure API-focused pages clearly:

1. **What it does** - One sentence
2. **Basic usage** - Simple example
3. **Common use cases** - 2-3 practical examples
4. **Important constraints** - Warnings/limitations
5. **Reference** - Full parameter documentation (if applicable)

## Common Patterns Sections

For complex features, include a "Common Patterns" section with 3-5 real-world examples:

```markdown
## Common patterns

### Integrating third-party libraries

[Practical example with real library]

### Syncing with localStorage

[Actual code that works]
```

**Guidelines:**
- Use real library names (Google Maps, Stripe, etc.)
- Show complete, working examples
- Address actual developer pain points
- Keep examples concise but functional

## Best Practices Sections

For features with performance/debugging considerations, add a "Best Practices" section:

```markdown
## Best practices

### When to use X vs Y

**Use X when:**
- Specific scenario
- Another scenario

**Use Y when:**
- Different scenario

### Performance considerations

- Concrete tip
- Another tip with example
```

**Guidelines:**
- Provide decision-making frameworks
- Include debugging tips
- Address common gotchas
- Use concrete examples, not vague advice

## Documentation Anti-Patterns

**Avoid these patterns:**

❌ Using emojis (unless user explicitly requests)
❌ Superlatives ("amazing", "powerful", "beautiful")
❌ Marketing language ("Livewire makes it easy!")
❌ Vague examples (`// Do something here`)
❌ Hypothetical scenarios ("Imagine you want to...")
❌ Second-person tutorial style ("You can now...")
❌ Starting sentences with "Now," or "Next,"
❌ Long paragraphs (break up after 3-4 lines)
❌ Code without context (always show usage)
❌ Duplicate examples (each example should teach something new)

**Prefer these patterns:**

✅ Clear, factual statements
✅ Active voice ("Livewire provides...")
✅ Concrete, working examples
✅ Progressive disclosure (simple → complex)
✅ Consistent component names within a page
✅ Short paragraphs and clear headings
✅ Real-world use cases
✅ Cross-references to related features

## Code Style Conventions

### PHP

- Modern PHP syntax (typed properties, constructor promotion when appropriate)
- No unnecessary docblocks for simple properties
- Import statements ordered by line length (longest first)
- Use strict types where it improves clarity
- Follow Laravel conventions

### Blade

- Consistent indentation (4 spaces)
- Use `wire:` directives over `x-` when both work
- Show Alpine.js integration where relevant
- Self-closing tags for components: `<livewire:post.create />`

### JavaScript

- Modern ES6+ syntax (arrow functions, const/let, template literals)
- Consistent naming: `$wire` for Livewire, Alpine.js for Alpine
- Show both inline and component scripts as appropriate
- Clear event handler examples

## Examples Should Teach

Every code example should have a purpose:

**Good:**
```php
// Shows how to pass parameters to mount()
Livewire::test('post.edit', ['post' => $post])
    ->assertSet('title', 'Existing post title');
```

**Bad:**
```php
// Generic example with no context
Livewire::test('some.component')
    ->assertSet('property', 'value');
```

**Guidelines:**
- Use realistic property/method names
- Show the pattern, not just the syntax
- Include just enough context to be clear
- Don't repeat the same example with minor variations
- Comments should explain "why", not "what"

## Content Density

Balance completeness with scanability:

**Too sparse:**
```markdown
## Feature

This feature does something.

[single basic example]
```

**Too dense:**
```markdown
## Feature

[wall of text explaining every edge case]
[10 similar examples]
[exhaustive parameter listing up front]
```

**Just right:**
```markdown
## Feature

[What it is - 1 sentence]

[Basic example]

[2-3 common use cases with examples]

[Important constraints with tip box]

## Reference
[Detailed parameters for advanced users]
```

## Updating Existing Documentation

When improving existing docs:

1. **Read the entire page first** - Understand the flow
2. **Identify the learning progression** - Basic → Advanced
3. **Check for consistency** - Terminology, component names, style
4. **Update examples to single-file format** - Unless class-based needed
5. **Move reference content down** - Tutorial content should come first
6. **Add cross-references** - Link to related features
7. **Check for outdated patterns** - Ensure Livewire 4 conventions
8. **Test code mentally** - Would this example actually work?

## Final Checklist

Before considering documentation complete:

- [ ] All component examples use single-file format (unless class-based needed)
- [ ] Testing examples use Pest (unless PHPUnit section)
- [ ] Layout files match official stub format
- [ ] Correct terminology (messages not commits, memoize not cache)
- [ ] Script tags don't use `@script` wrapper for single-file components
- [ ] @ symbols properly escaped in tip/warning/info blocks
- [ ] Cross-references to related features included
- [ ] Code examples are complete and realistic
- [ ] Reference sections at bottom (not interrupting tutorial flow)
- [ ] Callouts used appropriately (not overused)
- [ ] No emojis, superlatives, or marketing language
- [ ] Clear progression from simple to advanced
- [ ] Examples teach specific patterns, not just syntax
