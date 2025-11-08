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
