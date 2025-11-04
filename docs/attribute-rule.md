The `#[Rule]` attribute has been deprecated in favor of `#[Validate]`.

## Migration

When Livewire v3 first launched, it used the term "Rule" for validation attributes. Due to naming conflicts with Laravel's Rule objects, this was changed to `#[Validate]`.

Both `#[Rule]` and `#[Validate]` are supported in Livewire, but it is strongly recommended to migrate to `#[Validate]` to stay current.

### Before (deprecated)

```php
<?php

use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    #[Rule('required|min:3')]
    public $title = '';
};
```

### After (recommended)

```php
<?php

use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|min:3')]
    public $title = '';
};
```

Simply replace:
- `use Livewire\Attributes\Rule;` with `use Livewire\Attributes\Validate;`
- `#[Rule(...)]` with `#[Validate(...)]`

## Learn more

For complete documentation on validation in Livewire, including the `#[Validate]` attribute, see:

- [Validation documentation](/docs/4.x/validation)
- [`#[Validate]` attribute documentation](/docs/4.x/attribute-validate)
