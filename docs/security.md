It's important to make sure your Livewire apps are secure and don't expose any application vulnerabilities. Livewire has internal security features to handle many cases, however, there are times when it's up to your application code to keep your components secure.

## Authorizing action parameters

Livewire actions are extremely powerful, however, any parameters passed to Livewire actions are mutable on the client and should be treated as un-trusted user input.

Arguably the most common security pitfall in Livewire is failing to validate and authorize Livewire action calls before persisting changes to the database.

Here is an example of an insecurity resulting from a lack of authorization:

```php
<?php

use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    // ...

    public function delete($id)
    {
        // INSECURE!

        $post = Post::find($id);

        $post->delete();
    }
}
```

```html
<button wire:click="delete({{ $post->id }})">Delete Post</button>
```


The reason the above example is insecure is that `wire:click="delete(...)"` can be modified in the browser to pass ANY post ID a malicious user wishes.

Action parameters (like `$id` in this case) should be treated the same as any untrusted input from the browser.

Therefore, to keep this application secure and prevent a user from deleting another user's post, we must add authorization to the `delete()` action.

First, let's create a [Laravel Policy](https://laravel.com/docs/authorization#creating-policies) for the Post model by running the following command:

```bash
php artisan make:policy PostPolicy --model=Post
```

After running the above command, a new Policy will be created inside `app/Policies/PostPolicy.php`. We can then update its contents with a `delete` method like so:

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine if the given post can be deleted by the user.
     */
    public function delete(?User $user, Post $post): bool
    {
        return $user?->id === $post->user_id;
    }
}
```

Now, we can use the `$this->authorize()` method from the Livewire component to ensure the user owns the post before deleting it:

```php
public function delete($id)
{
    $post = Post::find($id);

    // If the user doesn't own the post,
    // an AuthorizationException will be thrown...
    $this->authorize('delete', $post); // [tl! highlight]

    $post->delete();
}
```

Further reading:
* [Laravel Gates](https://laravel.com/docs/authorization#gates)
* [Laravel Policies](https://laravel.com/docs/authorization#creating-policies)

## Authorizing public properties

Similar to action parameters, public properties in Livewire should be treated as un-trusted input from the user.

Here is the same example from above about deleting a post, written insecurely in a different manner:

```php
<?php

use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    public $postId;

    public function mount($postId)
    {
        $this->postId = $postId;
    }

    public function delete()
    {
        // INSECURE!

        $post = Post::find($this->postId);

        $post->delete();
    }
}
```

```html
<button wire:click="delete">Delete Post</button>
```

As you can see, instead of passing the `$postId` as a parameter to the `delete` method from `wire:click`, we are storing it as a public property on the Livewire component.

The problem with this approach is that any malicious user can inject a custom element onto the page such as:

```html
<input type="text" wire:model="postId">
```

This would allow them to freely modify the `$postId` before pressing "Delete Post". Because the `delete` action doesn't authorize the value of `$postId`, the user can now delete any post in the database, whether they own it or not.

To protect against this risk, there are two possible solutions:

### Using model properties

When setting public properties, Livewire treats models differently than plain values such as strings and integers. Because of this, if we instead store the entire post model as a property on the component, Livewire will ensure the ID is never tampered with.

Here is an example of storing a `$post` property instead of a simple `$postId` property:

```php
<?php

use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    public Post $post;

    public function mount($postId)
    {
        $this->post = Post::find($postId);
    }

    public function delete()
    {
        $this->post->delete();
    }
}
```

```html
<button wire:click="delete">Delete Post</button>
```

This component is now secured because there is no way for a malicious user to change the `$post` property to a different Eloquent model.

### Locking the property
Another way to prevent properties from being set to unwanted values is to use [the `#[Locked]` attribute](/docs/4.x/attribute-locked). Locking properties is done by applying the `#[Locked]` attribute. Now if users attempt to tamper with this value an error will be thrown.

Note that properties with the Locked attribute can still be changed in the back-end, so care still needs to taken that untrusted user input is not passed to the property in your own Livewire functions.

```php
<?php

use App\Models\Post;
use Livewire\Component;
use Livewire\Attributes\Locked;

class ShowPost extends Component
{
    #[Locked] // [tl! highlight]
    public $postId;

    public function mount($postId)
    {
        $this->postId = $postId;
    }

    public function delete()
    {
        $post = Post::find($this->postId);

        $post->delete();
    }
}
```

### Authorizing the property

If using a model property is undesired in your scenario, you can of course fall-back to manually authorizing the deletion of the post inside the `delete` action:

```php
<?php

use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    public $postId;

    public function mount($postId)
    {
        $this->postId = $postId;
    }

    public function delete()
    {
        $post = Post::find($this->postId);

        $this->authorize('delete', $post); // [tl! highlight]

        $post->delete();
    }
}
```

```html
<button wire:click="delete">Delete Post</button>
```

Now, even though a malicious user can still freely modify the value of `$postId`, when the `delete` action is called, `$this->authorize()` will throw an `AuthorizationException` if the user does not own the post.

Further reading:
* [Laravel Gates](https://laravel.com/docs/authorization#gates)
* [Laravel Policies](https://laravel.com/docs/authorization#creating-policies)

## Middleware

When a Livewire component is loaded on a page containing route-level [Authorization Middleware](https://laravel.com/docs/authorization#via-middleware), like so:

```php
Route::livewire('/post/{post}', App\Livewire\UpdatePost::class)
    ->middleware('can:update,post'); // [tl! highlight]
```

Livewire will ensure those middlewares are re-applied to subsequent Livewire network requests. This is referred to as "Persistent Middleware" in Livewire's core.

Persistent middleware protects you from scenarios where the authorization rules or user permissions have changed after the initial page-load.

Here's a more in-depth example of such a scenario:

```php
Route::livewire('/post/{post}', App\Livewire\UpdatePost::class)
    ->middleware('can:update,post'); // [tl! highlight]
```

```php
<?php

use App\Models\Post;
use Livewire\Component;
use Livewire\Attributes\Validate;

class UpdatePost extends Component
{
    public Post $post;

    #[Validate('required|min:5')]
    public $title = '';

    public $content = '';

    public function mount()
    {
        $this->title = $this->post->title;
        $this->content = $this->post->content;
    }

    public function update()
    {
        $this->post->update([
            'title' => $this->title,
            'content' => $this->content,
        ]);
    }
}
```

As you can see, the `can:update,post` middleware is applied at the route-level. This means that a user who doesn't have permission to update a post cannot view the page.

However, consider a scenario where a user:
* Loads the page
* Loses permission to update after the page loads
* Tries updating the post after losing permission

Because Livewire has already successfully loaded the page you might ask yourself: "When Livewire makes a subsequent request to update the post, will the `can:update,post` middleware be re-applied? Or instead, will the un-authorized user be able to update the post successfully?"

Because Livewire has internal mechanisms to re-apply middleware from the original endpoint, you are protected in this scenario.

### Configuring persistent middleware

By default, Livewire persists the following middleware across network requests:

```php
\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
\Laravel\Jetstream\Http\Middleware\AuthenticateSession::class,
\Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
\Illuminate\Routing\Middleware\SubstituteBindings::class,
\App\Http\Middleware\RedirectIfAuthenticated::class,
\Illuminate\Auth\Middleware\Authenticate::class,
\Illuminate\Auth\Middleware\Authorize::class,
```

If any of the above middlewares are applied to the initial page-load, they will be persisted (re-applied) to any future network requests.

However, if you are applying a custom middleware from your application on the initial page-load, and want it persisted between Livewire requests, you will need to add it to this list from a [Service Provider](https://laravel.com/docs/providers#main-content) in your app like so:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::addPersistentMiddleware([ // [tl! highlight:2]
            App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    }
}
```

If a Livewire component is loaded on a page that uses the `EnsureUserHasRole` middleware from your application, it will now be persisted and re-applied to any future network requests to that Livewire component.

> [!warning] Middleware arguments are not supported
> Livewire currently doesn't support middleware arguments for persistent middleware definitions.
>
> ```php
> // Bad...
> Livewire::addPersistentMiddleware(AuthorizeResource::class.':admin');
>
> // Good...
> Livewire::addPersistentMiddleware(AuthorizeResource::class);
> ```


### Applying global Livewire middleware

Alternatively, if you wish to apply specific middleware to every single Livewire update network request, you can do so by registering your own Livewire update route with any middleware you wish:

```php
Livewire::setUpdateRoute(function ($handle) {
	return Route::post('/livewire/update', $handle)
        ->middleware(App\Http\Middleware\LocalizeViewPaths::class);
});
```

Any Livewire AJAX/fetch requests made to the server will use the above endpoint and apply the `LocalizeViewPaths` middleware before handling the component update.

Learn more about [customizing the update route on the Installation page](https://livewire.laravel.com/docs/installation#configuring-livewires-update-endpoint).

## Snapshot checksums

Between every Livewire request, a snapshot is taken of the Livewire component and sent to the browser. This snapshot is used to re-build the component during the next server round-trip.

[Learn more about Livewire snapshots in the Hydration documentation.](https://livewire.laravel.com/docs/hydration#the-snapshot)

Because fetch requests can be intercepted and tampered with in a browser, Livewire generates a "checksum" of each snapshot to go along with it.

This checksum is then used on the next network request to verify that the snapshot hasn't changed in any way.

If Livewire finds a checksum mismatch, it will throw a `CorruptComponentPayloadException` and the request will fail.

This protects against any form of malicious tampering that would otherwise result in granting users the ability to execute or modify unrelated code.

## Protecting your APP_KEY

Livewire's snapshot checksums are signed using your application's `APP_KEY`. If an attacker gains access to your `APP_KEY`, they can forge valid checksums and craft malicious payloads — potentially achieving **remote code execution** on your server.

This was demonstrated in [CVE-2025-54068](https://github.com/livewire/livewire/security/advisories/GHSA-29cq-5w36-x7w3), where a leaked `APP_KEY` combined with Livewire's hydration system enabled full server compromise.

> [!warning] APP_KEY exposure is critical
> A leaked `APP_KEY` is not just a session-hijacking risk — it can lead to remote code execution through Livewire's component hydration. Treat your `APP_KEY` as you would a database password or private key.

To protect your `APP_KEY`:

- **Never commit it to version control.** Ensure `.env` is in your `.gitignore` and audit your repository history for accidental exposure.
- **Rotate it immediately if exposed.** If your `APP_KEY` has ever been committed to a public repository, leaked in logs, or exposed through a `.env` file accessible via the web, rotate it immediately. Generate a new key with `php artisan key:generate` and invalidate all existing sessions.
- **Restrict access in production.** Only infrastructure engineers who manage deployments should have access to production environment variables. Use your hosting provider's secrets management (e.g., environment variables in Forge, Vapor, or your CI/CD pipeline) rather than `.env` files on disk.
- **Audit third-party packages.** Be cautious of packages that log, transmit, or expose configuration values — a package that dumps `config('app.key')` to a log file or error reporting service could inadvertently leak your key.

## Custom route security

When you customise Livewire's update endpoint using `Livewire::setUpdateRoute()`, keep the following in mind:

### Use unpredictable paths

By default, Livewire uses a hash-based endpoint (`/livewire-{hash}/update`) that varies per installation. This makes it harder for automated scanners to locate the endpoint.

If you override this with a custom route, **avoid using predictable paths** like `/livewire/update` — this is the well-known v3 default path that exploitation tools specifically target.

Instead, use Livewire's built-in hash:

```php
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

Livewire::setUpdateRoute(function ($handle) {
    return Route::post(EndpointResolver::updatePath(), $handle)
        ->middleware(['web', 'auth']);
});
```

This preserves the scanner resistance of the default endpoint while allowing you to add custom middleware.

### Always include the `web` middleware

Livewire's default route is registered within the `web` middleware group, which provides CSRF protection via Laravel's `VerifyCsrfToken` middleware. If your custom route doesn't include `web`, **CSRF protection is lost entirely** and attackers may be able to submit forged requests to your components.

Always include `->middleware('web')` (or a middleware group that includes it) on your custom route.

## Monitoring for attacks

Livewire rate-limits checksum failures to slow down brute-force attacks on the update endpoint. However, any checksum failure is unusual — legitimate Livewire requests should never produce one unless a user's session has expired.

Repeated checksum failures from the same IP address are a strong indicator that someone is probing or attempting to exploit the endpoint.

You can listen for checksum failures using Livewire's `checksum.fail` hook and forward them to your monitoring or logging system:

```php
use Livewire\Livewire;

Livewire::listen('checksum.fail', function ($checksum, $expected, $snapshot) {
    Log::warning('Livewire checksum failure', [
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'component' => $snapshot['memo']['name'] ?? 'unknown',
    ]);
});
```

> [!tip] Set up alerts
> Consider setting up alerts in your monitoring system (e.g., Sentry, Datadog, or Laravel Telescope) for checksum failure patterns. A burst of failures from a single IP or targeting a specific component warrants investigation.

### Trusted proxies

If your application is behind a reverse proxy or load balancer, ensure you have [trusted proxies](https://laravel.com/docs/requests#configuring-trusted-proxies) configured correctly. Without this, `request()->ip()` may return the proxy's IP address rather than the client's real IP, undermining both rate limiting and monitoring.

## Strict property types

Declaring strict types on your component properties provides an additional layer of defence against payload manipulation. When a property has a strict type declaration (e.g., `int`, `string`, `bool`, or an enum), PHP will reject values that don't match the expected type — preventing attackers from substituting unexpected data structures.

For example, consider the difference between:

```php
// Loosely typed — accepts any value PHP can coerce:
public $quantity;

// Strictly typed — rejects non-integer values:
public int $quantity = 0;
```

A loosely-typed property can be set to an array, an object, or any other value that might trigger unexpected behaviour during processing. A strictly-typed property will throw a `TypeError` if anything other than the declared type is provided.

> [!info] Type strictness as a security boundary
> During the analysis of [CVE-2025-54068](https://github.com/livewire/livewire/security/advisories/GHSA-29cq-5w36-x7w3), security researchers noted that strictly-typed properties were **not exploitable** — the attack relied on properties that accepted arbitrary types. Strict type declarations effectively closed the exploitation path for those properties.

**Recommendations:**

- **Declare types on all public properties.** Use `int`, `string`, `float`, `bool`, typed arrays, enums, or model types — never leave a public property untyped.
- **Use enums for constrained values.** If a property should only accept specific values (e.g., a status or category), use a [backed enum](https://www.php.net/manual/en/language.enumerations.backed.php) instead of a plain string.
- **Combine with `#[Locked]`.** Properties that should never be modified from the frontend should use both a strict type and the `#[Locked]` attribute for defence in depth.

```php
<?php

use App\Enums\Status;
use Livewire\Component;
use Livewire\Attributes\Locked;

class UpdateOrder extends Component
{
    #[Locked]
    public int $order_id;

    public string $note = '';

    public Status $status;

    // ...
}
```
