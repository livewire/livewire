Livewire allows you to lazy load components that would otherwise slow down the initial page load.

For example, imagine you have a `Revenue` component which contains a slow database query in `mount()`:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;

class Revenue extends Component
{
    public $amount;

    public function mount()
    {
        // Slow database query...
        $this->amount = Transaction::monthToDate()->sum('amount');
    }

    public function render()
    {
        return view('livewire.revenue');
    }
}
```

```blade
<div>
    Revenue this month: {{ $amount }}
</div>
```

Without lazy loading, this component would delay the loading of the entire page and make your entire application feel slow.

To enable lazy loading, you can pass the `lazy` parameter into the component:

```blade
<livewire:revenue lazy />
```

Now, instead of loading the component right away, Livewire will skip this component, loading the page without it. Then, after loading the rest of the page, Livewire will make a network request to fully load this component on the page.

## Rendering placeholder HTML

By default, Livewire will insert an empty `<div></div>` for your component before it is fully loaded. As the component will initially be invisible to users, it can be jarring when the component suddenly appears on the page.

To signal to your users that the component is being loaded, you can define a `placeholder()` method to render any kind of placeholder HTML you like, including loading spinners and skeleton placeholders:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;

class Revenue extends Component
{
    public $amount;

    public function mount()
    {
        // Slow database query...
        $this->amount = Transaction::monthToDate()->sum('amount');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
            <svg>...</svg>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.revenue');
    }
}
```

Because the above component specifies a "placeholder" by returning HTML from a `placeholder()` method, the user will see an SVG loading spinner on the page until the component is fully loaded.

## Passing in props

In general, you can treat `lazy` components the same as normal components since you can still pass data into them from outside.

For example, here's a scenario where you might pass a time interval into the `Revenue` component from a parent component:

```blade
<input type="date" wire:model="start">
<input type="date" wire:model="end">

<livewire:revenue lazy :start="$start" :end="$end" />
```

You can accept this data in `mount()` just like any other component:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;

class Revenue extends Component
{
    public $amount;

    public function mount($start, $end)
    {
        // Expensive database query...
        $this->amount = Transactions::between($start, $end)->sum('amount');
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
            <svg>...</svg>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.revenue');
    }
}
```

However, unlike a normal component load, a `lazy` component has to serialize or "dehydrate" any passed-in properties and temporarily store them on the client-side until the component is fully loaded.

For example, you might want to pass in an Eloquent model to the `Revenue` component like so:

```blade
<livewire:revenue lazy :user="$user" />
```

In a normal component, the actual PHP in-memory `$user` model would be passed into the `mount()` method of `Revenue`. However, because we won't run `mount()` until the next network request, Livewire will internally serialize `$user` to JSON and then re-query it from the database before the next request is handled.

Typically, this serialization should not cause any behavioral differences in your application.
