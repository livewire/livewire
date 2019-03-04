# Generating Components

You can manually create and store Livewire where you choose, however, you can take advantage the helpful `livewire:make` artisan command to handle this automatically.

To generate a `Counter` component, we can run:
```
php artisan livewire:make Counter
```

This will generate a new file called: `App\Http\Livewire\Counter.php` with the following contents:

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

To automatically generate the blade view, add the `--view` flag to the command:

```
php artisan livewire:make Counter --view
```

By adding this flag, the command will also generate a blade view in: `resources/views/livewire/counter.blade.php`
