## Livewire page routing system

This document outlines how Livewire handles page-level routing, where entire routes are powered by Livewire components rather than traditional controllers. This enables developers to create full-page interactive experiences with Livewire's reactive capabilities.

In V3, page routing requires manual controller-style registration. In V4, we're introducing a streamlined routing system with built-in layout support and intelligent namespacing.

## V3 system

### Current Registration Approach

In Livewire V3, page routing is accomplished by registering component classes as invokable controllers:

```php
// Traditional Laravel routing
Route::get('/dashboard', DashboardComponent::class);
Route::get('/users/{user}', UserProfileComponent::class);
Route::get('/posts/{post}/edit', EditPostComponent::class);
```

This works because Livewire components implement the `__invoke()` method through the `HandlesPageComponents` trait, making them function as invokable controllers.

### Component Structure

V3 page components are standard PHP classes:

```php
<?php // app/Livewire/Dashboard.php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $stats = [];

    public function mount()
    {
        $this->stats = [
            'users' => User::count(),
            'posts' => Post::count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
```

### Layout Configuration

Layouts in V3 are configured in the component's `render()` method:

```php
public function render()
{
    return view('livewire.component')
        ->layout('layouts.app', ['title' => 'Page Title'])
        ->slot('main');  // optional custom slot
}
```

Or using the `#[Layout]` attribute:

```php
use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['title' => 'Dashboard'])]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard');
    }
}
```

## V4 system

V4 introduces a view-first page routing system that dramatically simplifies page component creation and supports inline layout configuration.

### Simplified Route Registration

V4 provides a dedicated `Livewire::route()` helper for clean page registration:

```php
// Simple page routes
Livewire::route('/dashboard', 'pages::dashboard');
Livewire::route('/profile', 'pages::profile');
Livewire::route('/settings', 'pages::settings');

// Routes with parameters
Livewire::route('/users/{user}', 'pages::user-profile');
Livewire::route('/posts/{post}/edit', 'pages::edit-post');
```

### View-First Page Components

Pages are now single-file components with inline layouts:

```php
{{-- resources/views/pages/dashboard.blade.php --}}
@layout('layouts.app', ['title' => 'Dashboard', 'active' => 'dashboard'])

@php
new class extends Livewire\Component {
    public $stats = [];

    public function mount()
    {
        $this->stats = [
            'users' => User::count(),
            'posts' => Post::count(),
        ];
    }
}
@endphp

<div class="dashboard">
    <h1>Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Users</h3>
            <p>{{ $stats['users'] }}</p>
        </div>

        <div class="stat-card">
            <h3>Posts</h3>
            <p>{{ $stats['posts'] }}</p>
        </div>
    </div>
</div>
```

### Layout Integration

The `@layout()` directive in the frontmatter provides inline layout configuration:

```php
{{-- Simple layout --}}
@layout('layouts.app')

{{-- Layout with data --}}
@layout('layouts.app', ['title' => 'Dashboard', 'meta' => ['description' => 'Admin dashboard']])

{{-- External class with layout --}}
@layout('layouts.admin', ['section' => 'users'])
@php(new App\Livewire\AdminUserManager)
```

This compiles down to the `#[Layout]` attribute on the generated class:

```php
#[Layout('layouts.app', ['title' => 'Dashboard', 'meta' => ['description' => 'Admin dashboard']])]
class Dashboard_abc123 extends Livewire\Component
{
    // Component logic...
}
```

### Default Namespacing

V4 introduces automatic namespacing for better organization:

#### Default Namespaces

- **Pages**: `pages::` → `resources/views/pages/`
- **Layouts**: `layouts::` → `resources/views/layouts/`
- **Components**: `components::` → `resources/views/components/`

#### Custom Namespaces

You can register custom namespaces for different sections:

```php
// Register admin pages namespace
Livewire::namespace('admin-pages', resource_path('views/admin/pages'));
Livewire::namespace('admin-layouts', resource_path('views/admin/layouts'));

// Use in routes
Livewire::route('/admin/users', 'admin-pages::users');
Livewire::route('/admin/settings', 'admin-pages::settings');
```

#### Layout Namespace Resolution

Layout references automatically resolve namespaces:

```php
@layout('layouts::app')           // → resources/views/layouts/app.blade.php
@layout('admin-layouts::panel')   // → resources/views/admin/layouts/panel.blade.php
```

### Route Parameter Handling

V4 automatically injects route parameters into component properties:

```php
{{-- resources/views/pages/user-profile.blade.php --}}
@layout('layouts.app', ['title' => 'User Profile'])

@php
new class extends Livewire\Component {
    public User $user;  // Automatically injected from {user} parameter

    public function mount()
    {
        // User is already resolved and available
        $this->loadUserStats();
    }

    private function loadUserStats()
    {
        // Component logic...
    }
}
@endphp

<div class="user-profile">
    <h1>{{ $user->name }}</h1>
    <p>{{ $user->email }}</p>
</div>
```

### Advanced Features

#### Nested Page Organization

```php
// Nested page structure
Livewire::route('/admin/users', 'pages::admin.users.index');
Livewire::route('/admin/users/create', 'pages::admin.users.create');
Livewire::route('/admin/users/{user}/edit', 'pages::admin.users.edit');

// Resolves to:
// resources/views/pages/admin/users/index.blade.php
// resources/views/pages/admin/users/create.blade.php
// resources/views/pages/admin/users/edit.blade.php
```

#### Multiple Layout Support

```php
{{-- Different layouts for different sections --}}
@layout('layouts::admin', ['title' => 'Admin Panel'])  // Admin pages
@layout('layouts::app', ['title' => 'Dashboard'])      // Regular pages
@layout('layouts::auth', ['title' => 'Login'])         // Auth pages
```

#### Layout Inheritance

```php
{{-- Base layout --}}
@layout('layouts::base', ['title' => 'App'])
```

## Current V4 Implementation Status

### What's Been Implemented

The V4 page routing system has been successfully implemented with comprehensive compiler integration and layout support.

### File Structure

The page routing system integrates with existing V4 components:

```
src/v4/
├── Registry/
│   ├── ComponentViewPathResolver.php    # View path resolution
│   └── ComponentViewPathResolverUnitTest.php
├── Compiler/
│   ├── SingleFileComponentCompiler.php  # Layout compilation
│   ├── ParsedComponent.php             # Layout parsing
│   └── SingleFileComponentCompilerUnitTest.php
└── Page/
    ├── UnitTest.php                    # Page routing tests
    └── fixtures/
        ├── pages/dashboard.blade.php   # Test page component
        └── layouts/app.blade.php       # Test layout
```

### Core Integration Points

#### 1. **LivewireManager::route()**
**Location**: `src/LivewireManager.php`

The main route registration method:

```php
function route($uri, $component)
{
    $instance = $this->new($component);
    \Illuminate\Support\Facades\Route::get($uri, $instance::class);
}
```

#### 2. **Layout Directive Compilation**
**Location**: `src/v4/Compiler/SingleFileComponentCompiler.php`

Parses `@layout()` directives and compiles them to `#[Layout]` attributes:

```php
// Input
@layout('layouts.app', ['title' => 'Dashboard'])

// Generated output
#[\Livewire\Attributes\Layout('layouts.app', ['title' => 'Dashboard'])]
```

#### 3. **Component Resolution**
**Location**: `src/v4/IntegrateV4.php`

Integrates with the missing component resolver for automatic compilation:

```php
app('livewire')->resolveMissingComponent(function ($componentName) {
    $viewPath = $this->finder->resolve($componentName);
    $result = $this->compiler->compile($viewPath);
    // Load and return compiled class
});
```

### Features Implemented

#### ✅ **Livewire::route() Helper**
- Clean route registration syntax
- Automatic component instantiation
- Integration with Laravel's routing system

#### ✅ **@layout() Directive Support**
- Frontmatter layout configuration
- Data parameter support
- Compilation to #[Layout] attributes
- Works with both inline and external components

#### ✅ **View-First Page Components**
- Single-file component structure
- Automatic compilation and caching
- Support for both anonymous classes and external references

#### ✅ **Default Namespace Registration**
- `pages::` namespace automatically registered
- `layouts::` namespace for layout resolution
- Clean namespace resolution in route registration

#### ✅ **Route Parameter Injection**
- Automatic injection of route parameters
- Model binding support
- Integration with Laravel's route model binding

#### ✅ **Layout Data Passing**
- Array data support in @layout directive
- String, numeric, and boolean value handling
- Complex data structure support

### Testing

Comprehensive testing covers the complete page routing flow:

**Test Command**: `phpunit src/v4/Page/`

#### Page Routing Tests (1 test)
- Full page route registration and rendering
- Layout application and data passing
- Component compilation and execution

**Integration with Compiler Tests**: 45 tests covering layout compilation
**Integration with Registry Tests**: 12 tests covering view resolution

### Usage Examples

#### Basic Page Route

```php
// Route registration
Livewire::route('/dashboard', 'pages::dashboard');

// Page component (resources/views/pages/dashboard.blade.php)
@layout('layouts.app', ['title' => 'Dashboard'])

@php
new class extends Livewire\Component {
    public function render()
    {
        return $this->view();
    }
}
@endphp

<div>
    <h1>Welcome to Dashboard</h1>
</div>
```

#### Page with Parameters

```php
// Route registration
Livewire::route('/users/{user}', 'pages::user-profile');

// Page component (resources/views/pages/user-profile.blade.php)
@layout('layouts.app')

@php
new class extends Livewire\Component {
    public User $user;

    public function mount()
    {
        // $user is automatically injected
    }
}
@endphp

<div>
    <h1>{{ $user->name }}</h1>
</div>
```

#### Custom Namespace Usage

```php
// Register custom namespace
Livewire::namespace('admin', resource_path('views/admin'));

// Route registration
Livewire::route('/admin/dashboard', 'admin::dashboard');

// Page component (resources/views/admin/dashboard.blade.php)
@layout('admin::layout', ['title' => 'Admin Dashboard'])
@php(new App\Admin\DashboardComponent)

<div>Admin content here</div>
```

### Benefits of V4 Approach

#### 1. **Reduced Boilerplate**
- No separate PHP class files for simple pages
- Inline layout configuration
- Automatic parameter injection

#### 2. **Better Organization**
- Logical namespace structure
- Co-located layout configuration
- Clear file organization

#### 3. **Enhanced DX**
- Single-file editing experience
- Immediate layout configuration
- Reduced context switching

#### 4. **Performance Benefits**
- Compiled components with caching
- Efficient route resolution
- Minimal runtime overhead

### Next Steps

#### 1. **Enhanced Namespace Features**
- Automatic namespace discovery
- Nested namespace support
- Namespace-specific configuration

#### 2. **Layout Enhancements**
- Layout inheritance system
- Conditional layout application
- Dynamic layout switching

#### 3. **Development Tools**
- Route listing commands
- Page component generators
- Layout validation tools

#### 4. **Advanced Routing Features**
- Route groups for page components
- Middleware support for page routes
- Custom route parameter handling

The V4 page routing system provides a modern, streamlined approach to creating full-page Livewire experiences while maintaining full compatibility with Laravel's routing system and leveraging the power of single-file components with inline layout configuration.
