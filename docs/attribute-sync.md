The `#[Sync]` attribute keeps a property's shape consistent between PHP and JavaScript, so you can work with domain values instead of hand-written payload transforms in every component.

## Basic usage

Use `#[Sync]` on a property and provide either a built-in strategy or a custom codec:

```php
<?php

use App\Livewire\Codecs\MoneyCodec;
use Livewire\Attributes\Sync;
use Livewire\Component;

new class extends Component {
    #[Sync(MoneyCodec::class)]
    public Money $total;

    public function mount()
    {
        $this->total = new Money(1299, 'USD');
    }
};
```

## Real-world scenario: checkout totals

Without sync, checkout pages usually convert between:

- PHP value objects (`Money`)
- JavaScript objects (`{ amount, currency }`)
- wire payload values

With `#[Sync(MoneyCodec::class)]`, that translation lives in one place.

```js
Livewire.sync('App\\Livewire\\Codecs\\MoneyCodec', {
    fromServer: (value) => ({
        amount: value.amount,
        currency: value.currency,
        formatted: `$${(value.amount / 100).toFixed(2)}`,
    }),
    toServer: (value) => ({
        amount: value.amount,
        currency: value.currency,
    }),
})
```

This keeps totals, discounts, and tax math consistent across requests.

## Built-in strategies

For simple properties, pass a built-in strategy directly:

```php
#[Sync('int')]
public $quantity = 1;

#[Sync('bool')]
public $published = false;
```

Supported built-ins:

- `int` / `integer`
- `float` / `double`
- `bool` / `boolean`
- `string`
- `array`
- `object`

## Behavior

- `#[Sync]` is opt-in. Existing components are unchanged unless you apply the attribute.
- Custom codecs only support root-property updates. Deep updates (like `price.amount`) should be sent as a full `price` update.

## See also

- **[Properties](/docs/4.x/properties)** — Property lifecycle and updates
- **[JavaScript](/docs/4.x/javascript)** — Client-side Livewire APIs
- **[Synthesizers](/docs/4.x/synthesizers)** — Serialization internals
