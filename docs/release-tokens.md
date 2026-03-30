Livewire includes a release token mechanism that detects when a browser tab has gone stale after a new deployment. When a mismatch is found, Livewire returns a `419` response and prompts the user to refresh the page, preventing confusing errors from outdated component state.

## How it works

Each time a Livewire component is rendered, a release token is stored in the component's snapshot. On every subsequent request, Livewire compares the token from the snapshot against the current server-side token. If they don't match — typically because the application was redeployed — Livewire throws a `LivewireReleaseTokenMismatchException` with a `419` status code.

The browser handles this by showing a confirmation dialog: _"This page has expired. Would you like to refresh the page?"_

## The release token

The release token is composed of three parts concatenated together:

| Part | Source | Purpose |
|------|--------|---------|
| Internal token | Livewire's internal version marker | Changes between Livewire releases that alter the snapshot structure |
| Application token | `config('livewire.release_token')` | Changed by you during deployments |
| Component token | `YourComponent::releaseToken()` | Changed per-component when its contract changes |

A mismatch in any of the three parts triggers the expired page dialog.

## Changing the token on deploy

The most common use case is updating the application-level release token in `config/livewire.php` during a deployment:

```php
'release_token' => 'a',
```

By changing this value (for example, incrementing it to `'b'` or setting it to a timestamp/commit hash), any browser tab that was opened before the deploy will be prompted to refresh on its next Livewire request:

```php
'release_token' => 'b',
```

This is useful for zero-downtime deployments where the new release includes changes to component properties, snapshot structure, or Blade templates that would cause errors if processed against the old snapshot.

> [!tip] Automate it in your deploy script
> Set the release token dynamically in your deployment pipeline so you don't need to remember to change it manually:
>
> ```php
> 'release_token' => env('RELEASE_TOKEN', 'a'),
> ```
>
> Then in your deploy script or CI:
> ```bash
> RELEASE_TOKEN=$(git rev-parse --short HEAD)
> ```

## Per-component release tokens

For more granular control, override the `releaseToken()` method on a component. This is useful when a specific component's contract changes (e.g. renamed properties, restructured data) but the rest of the application hasn't changed:

```php
<?php // resources/views/components/⚡dashboard.blade.php

use Livewire\Component;

new class extends Component {
    public static function releaseToken(): string
    {
        return 'v2';
    }

    // ...
};
?>

<div>
    <!-- ... -->
</div>
```

Changing this return value invalidates only browser sessions that have a stale snapshot of this particular component, while leaving other components unaffected.

## See also

- **[Security](/docs/4.x/security)** — Snapshot checksums and payload protection
- **[Hydration](/docs/4.x/hydration)** — How snapshots and dehydration work
- **[Troubleshooting](/docs/4.x/troubleshooting)** — Common issues and debugging tips
