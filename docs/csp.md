# CSP (Content Security Policy) Build

Livewire offers a CSP-safe build that allows you to use Livewire applications in environments with strict Content Security Policy (CSP) headers that prohibit `'unsafe-eval'`.

## What is Content Security Policy (CSP)?

Content Security Policy (CSP) is a security standard that helps prevent various types of attacks, including Cross-Site Scripting (XSS) and code injection attacks. CSP works by allowing web developers to control which resources the browser is allowed to load and execute.

One of the most restrictive CSP directives is `'unsafe-eval'`, which when omitted, prevents JavaScript from executing dynamic code through functions like `eval()`, `new Function()`, and similar constructs that compile and execute strings as code at runtime.

### Why CSP Affects Livewire

By default, Livewire (and its underlying Alpine.js framework) uses `new Function()` declarations to compile and execute JavaScript expressions from HTML attributes like:

```html
<button wire:click="$set('count', count + 1)">Increment</button>
<div wire:show="user.role === 'admin'">Admin panel</div>
```

While this approach is much faster and safer than using `eval()` directly, it still violates the `'unsafe-eval'` CSP directive that many security-conscious applications enforce.

## Enabling CSP-Safe Mode

To enable Livewire's CSP-safe mode, you need to modify your application's configuration:

### Configuration

In your `config/livewire.php` file, set the `csp_safe` option to `true`:

```php
'csp_safe' => true,
```

## Impact on Alpine.js

**Important**: When you enable CSP-safe mode in Livewire, it also affects all Alpine.js functionality in your application. Alpine will automatically use its CSP-safe evaluator, which means all Alpine expressions throughout your app will be subject to the same parsing limitations.

This is where most developers will notice the constraints, as Alpine expressions tend to be more complex than typical Livewire expressions.

## What's Supported

The CSP build supports most common JavaScript expressions you'd use in Livewire:

### Basic Livewire Expressions
```html
<!--  These work -->
<button wire:click="increment">+</button>
<button wire:click="decrement">-</button>
<button wire:click="reset">Reset</button>
<button wire:click="save">Save</button>
<input wire:model="name">
<input wire:model.live="search">
```

### Method Calls with Parameters
```html
<!--  These work -->
<button wire:click="updateUser('John', 25)">Update User</button>
<button wire:click="setCount(42)">Set Count</button>
<button wire:click="saveData({ name: 'John', age: 30 })">Save Object</button>
```

### Property Access and Updates
```html
<!--  These work -->
<input wire:model="user.name">
<input wire:model="settings.theme">
<button wire:click="$set('user.active', true)">Activate</button>
<div wire:show="user.role === 'admin'">Admin Panel</div>
```

### Basic Expressions in Alpine
```html
<!--  These work -->
<div x-data="{ count: 0, name: 'Livewire' }" wire:ignore>
    <button x-on:click="count++">Increment</button>
    <span x-text="count"></span>
    <span x-text="'Hello ' + name"></span>
    <div x-show="count > 5">Count is high!</div>
</div>
```

## What's Not Supported

Some advanced JavaScript features won't work in CSP-safe mode:

### Complex JavaScript Expressions
```html
<!-- L These don't work -->
<button wire:click="items.filter(i => i.active).length">Count Active</button>
<div wire:show="users.some(u => u.role === 'admin')">Has Admin</div>
<button wire:click="(() => console.log('Hi'))()">Complex Function</button>
```

### Template Literals and Advanced Syntax
```html
<!-- L These don't work -->
<div x-text="`Hello ${name}`">Bad</div>
<div x-data="{ ...defaults }">Bad</div>
<button x-on:click="() => doSomething()">Bad</button>
```

### Dynamic Property Access
```html
<!-- L These don't work -->
<div wire:show="user[dynamicProperty]">Bad</div>
<button wire:click="this[methodName]()">Bad</button>
```

## Working around limitations

For complex Alpine expressions, use `Alpine.data()` or move logic to methods:

```html
<!-- Instead of complex inline expressions -->
<div x-data="users">
    <div x-show="hasActiveAdmins">Admin panel available</div>
    <span x-text="activeUserCount">0</span>
</div>

<script nonce="[nonce]">
    Alpine.data('users', () => ({
        users: ...,

        get hasActiveAdmins() {
            return this.users.filter(u => u.active && u.role === 'admin').length > 0;
        },

        get activeUserCount() {
            return this.users.filter(u => u.active).length;
        }
    }));
</script>
```

## CSP Headers Example

Here's an example of CSP headers that work with Livewire's CSP-safe build:

```
Content-Security-Policy: default-src 'self';
                        script-src 'nonce-[random]' 'strict-dynamic';
                        style-src 'self' 'unsafe-inline';
```

The key points:
- Remove `'unsafe-eval'` from your `script-src` directive
- Use nonce-based script loading with `'nonce-[random]'`
- Consider adding `'strict-dynamic'` for better compatibility with dynamically loaded scripts

## Performance Considerations

The CSP-safe build uses a different expression evaluator that:

- **Parsing**: Slightly slower initial parsing of expressions (usually negligible)
- **Runtime**: Similar runtime performance for simple expressions
- **Bundle size**: Slightly larger JavaScript bundle due to the custom parser

For most applications, these differences are imperceptible, but it's worth testing with your specific use case.

## Testing Your CSP Implementation

To verify your CSP setup is working:

1. **Enable CSP headers** in your web server or application
2. **Test in browser dev tools** - CSP violations will appear in the console
3. **Verify expressions work** - All your Livewire and Alpine expressions should function normally
4. **Check for console errors** - No `unsafe-eval` violations should appear

## When to Use CSP-Safe Mode

Consider using CSP-safe mode when:

- Your application requires strict CSP compliance
- You're building applications for security-sensitive environments
- Your organization's security policies prohibit `'unsafe-eval'`
- You're deploying to platforms with mandatory CSP restrictions