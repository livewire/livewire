Release tokens expire pages that are already open in users' browsers when a deployment would make their component state incompatible with the new code.

## Why release tokens exist

Livewire keeps a component's state in a [snapshot](/docs/4.x/hydration#the-snapshot) that lives in the browser. A tab opened before a deployment still holds a snapshot created by the old code, and its next interaction sends that snapshot to the new code.

Most deployments handle this without any help. New markup, new methods, and changes inside existing methods all take effect on the next request, because the new code renders the response.

Changes to a component's public properties are different. Take a `todos` component that gains a property populated in `mount()`:

```php
<?php // resources/views/components/⚡todos.blade.php

use Livewire\Component;

new class extends Component {
    public string $search = '';

    public array $filters; // [tl! highlight]

    public function mount()
    {
        $this->filters = ['status' => 'open']; // [tl! highlight]
    }
};
```

`mount()` only runs when a component first renders, so a page opened before the deployment never runs it. On that page's next request, `$filters` is an empty array instead of `['status' => 'open']`, and Livewire raises no error.

Release tokens prevent this by tagging every snapshot with a value you control. When the value in a snapshot no longer matches the code, Livewire rejects the request and asks the user to refresh instead of running new code against old state.

## When to change a token

The table below shows what a page opened before a deployment does on its next request if no token changes:

| Change in the deployment | Result on the old page |
|---|---|
| Added a public property set in `mount()` | Never receives the value from `mount()`, usually with no error |
| Removed or renamed a public property | Loses the old value with no error. A renamed property starts empty |
| Changed what a property's value means (dollars to cents, for example) | Treats the old value as the new format with no error |
| Changed a property's type | Throws a `TypeError` if the old value doesn't fit |
| Removed or renamed a public method | Throws a `MethodNotFoundException` if the page calls it |
| Removed or renamed a component | Shows the expired page dialog automatically |

The first three rows are the ones to watch for, since nothing signals that the component is running on bad state. Change a token when a deployment makes one of those changes and the affected page could still be open.

> [!info] Production and debug mode differ
> With `APP_DEBUG` off, the `TypeError` and `MethodNotFoundException` above return a 419, so users see the same expired page dialog. With it on, they see the full error page.

## How tokens are combined

Each snapshot stores its release token in `memo.release`. The value combines three separate tokens, each of which defaults to `a`:

```json
{
    "memo": {
        "name": "todos",
        "release": "a-a-a"
    }
}
```

In order, those tokens are:

| Token | Set by | Expires |
|---|---|---|
| Livewire | Livewire itself | Every component, when a Livewire release makes existing snapshots incompatible |
| Application | `release_token` in `config/livewire.php` | Every component in the application |
| Component | `releaseToken()` on a component | That component only |

On each subsequent request, Livewire generates this value from the current code and compares it with the one in the snapshot. Any difference is a mismatch. The values have no other meaning, so any string works.

## Expiring every component

The `release_token` config value expires every open page when it changes. If the config file isn't published yet, publish it first:

```bash
php artisan livewire:config
```

Then change the value:

```php
'release_token' => 'b',
```

Applications that cache their config need to run `php artisan config:cache` for the new value to take effect.

### Expiring on every deployment

The token can also come from the environment, which lets a deployment script set it to something like the current commit:

```php
'release_token' => env('LIVEWIRE_RELEASE_TOKEN', 'a'),
```

This removes the need to remember, but it interrupts every open tab on every deployment, including deployments that didn't touch a component. For most applications, changing the token by hand when a deployment needs it is the better trade-off.

> [!warning] Keep the token stable
> Don't use a value that changes between requests or servers, such as `time()`, `Str::random()`, or a hostname. A snapshot created on one request would fail on the next, and every interaction would show the expired page dialog. The same thing happens briefly during a rolling deployment if a page rendered by a new server sends its next request to an old one.

## Expiring a single component

When a change only affects one component, override the static `releaseToken()` method on that component so the rest of the application isn't interrupted:

```php
<?php // resources/views/components/⚡todos.blade.php

use Livewire\Component;

new class extends Component {
    public array $filters;

    public static function releaseToken(): string // [tl! highlight:3]
    {
        return 'b';
    }

    public function mount()
    {
        $this->filters = ['status' => 'open'];
    }
};
```

Livewire bundles components that update at the same time into a single request, and a stale component fails that entire request, including any up-to-date components in it. Those components work normally in requests of their own.

## What users see

A mismatch throws a `LivewireReleaseTokenMismatchException`, which returns a 419 response. In the browser, Livewire then shows a dialog:

```
This page has expired.
Would you like to refresh the page?
```

The action that triggered the request does not run. If the user accepts, the page reloads with a fresh snapshot, and anything they typed but hadn't saved is lost.

If they decline, the page stays as it is. The dialog doesn't appear a second time, so stale components fail silently until the page reloads. `wire:poll` also stops for every component on the page.

Livewire doesn't treat a mismatch as an error or as tampering:

* Laravel doesn't report the exception to your logs or error tracker
* Livewire checks the token before the [snapshot checksum](/docs/4.x/security#snapshot-checksums), so a stale snapshot never throws a `CorruptComponentPayloadException`
* A mismatch doesn't count toward the limit on failed checksums

## Customizing the expired page dialog

To replace the dialog, add a [request interceptor](/docs/4.x/javascript#request-interceptors) to your layout and call `preventDefault()` on 419 responses:

```blade
<script>
    document.addEventListener('livewire:init', () => {
        let notified = false

        Livewire.interceptRequest(({ onError }) => {
            onError(({ response, preventDefault }) => {
                if (response.status !== 419) return

                preventDefault()

                if (notified) return

                notified = true

                showRefreshBanner()
            })
        })
    })
</script>
```

Calling `preventDefault()` also opts out of the behavior described above, which is why this example tracks `notified` itself. `wire:poll` keeps sending requests, and the handler runs for each one that fails.

A 419 doesn't always mean a release token mismatch. An expired CSRF token returns the same status, as do the production failures described in [When to change a token](#when-to-change-a-token), so any custom message should cover a page that has gone out of date for any reason.

## See also

- **[Hydration](/docs/4.x/hydration)** — How snapshots are created and restored
- **[Security](/docs/4.x/security)** — Snapshot checksums and other protections
- **[JavaScript](/docs/4.x/javascript)** — Request interceptors and other client-side APIs
