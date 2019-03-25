# Generating Components

You can manually create and store Livewire where you choose, however, you can take advantage the helpful `livewire:make` artisan command to handle this automatically.

To generate a `Counter` component, we can run:
```bash
> php artisan livewire:make Counter
```

This will generate a new component and view file with the following contents:

`App\Http\Livewire\Counter.php`
```php
<?php

namespace App\Http\Livewire;

use Livewire\LivewireComponent;

class Counter extends LivewireComponent
{
    public function render()
    {
        return view('livewire.counter');
    }
}
```

`resources/views/livewire/counter.blade.php`
```php
<div>
    //
</div>
```
