Livewire allows you to lazy load components that would otherwise slow down the initial page load.

For example, if you have a component called `Revenue` with an expensive database query in `mount()` like so:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Transaction;

class Revenue extends Component
{
    public $amount;

    public function mount()
    {
        // Expensive database query...
        $this->amount = Transaction::monthToDate()->sum('amount');
    }

    public function render()
    {
        return view('livewire.revenue');
    }
}
```

```html
<div>
    Revenue this month: {{ $amount }} 
</div>
```

Without lazy loading, this component would delay the loading of the entire page, making the experience for the user unpleasant and making your application feel slow.

To use lazy loading, you can pass the `lazy` parameter into the component:

```html
<livewire:revenue lazy />
```

Now, instead of loading the component right away, Livewire will skip this component, loading the page without it; then, after loading, Livewire will make a network request and fully load this component on the page.

## Triggering the load immediately

It's worth noting that by default, Livewire will use Alpine's [`x-intersect`](https://alpinejs.dev/plugins/intersect) internally to only load the component when it enters the user's viewport.

For example, if the lazy loaded component is located at the bottom of the page, Livewire won't trigger the full load until the user scrolls to the component.

If you'd rather opt out of this behavior and load the component immediately after the page load, even if it's out of sight for the user, you can add the `on-load` attribute to the component like so:

```html
<livewire:revenue lazy on-load />
```

## Rendering placeholder HTML

By default, Livewire will load an empty `<div></div>` for your component before it is fully loaded. It will be invisible to users but still can be jarring when the component suddenly appears on the page. 

To signal to your users that the component is being loaded, you can render any kind of placeholder HTML you like, including loading spinners and skeleton placeholders using the `placeholder()` method in your component:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Transaction;

class Revenue extends Component
{
    public $amount;

    public function mount()
    {
        // Expensive database query...
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

Because the above component specifies a "placeholder" by returning HTML from `placeholder()`, the user will see an SVG loading spinner on the page until the component is fully loaded.

## Passing in props

In general, you can treat `lazy` components the same as normal components because you can pass data into them from outside.

For example, here's a scenario where you might pass in a time interval into the `Revenue` component from a parent component:

```html
<input type="date" wire:model="start">
<input type="date" wire:model="end">

<livewire:revenue lazy :start="$start" :end="$end" />
```

Now you can accept this data in `mount()` just like any other component:

```php
<?php

namespace App\Http\Livewire;

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

However, it's worth noting that, UNLIKE a normal component load, a `lazy` component has to serialize or "dehydrate" any passed-in properties and temporarily store them on the client-side until the component is fully loaded.

For example, you might want to pass in an Eloquent model to the `Revenue` component like so:

```html
<livewire:revenue lazy :user="$user" />
```

In a normal component, the actual PHP in-memory `$user` model would be passed into the `mount()` method of `Revenue`. However, because we won't run `mount()` until the next network request, Livewire will internally serialize `$user` to JSON and then re-query it from the database before the next request is handled.

You shouldn't notice this for the most part, but it's worth being aware of in case you run into any related edge cases.
