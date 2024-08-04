
* The component
    * Counter component
* Rendering the component
    * Mount
        * New up class
        * Dehydrate state
        * Embed inside HTML
        * Return HTML
* Initializing the component in JS
    * Finding wire:id elements
    * Extracting id and snapshot
    * Newing up object
* Sending an update
    * Registering event listeners
    * Sending a fetch request with updates and snapshot
* Receiving an update
    * Converting snapshot to component (hydrate)
    * Applying updates
    * Rendering component
    * Returning HTML and new snapshot
* Processing an update
    * Replacing with new snapshot
    * Replacing HTML with new HTML
        * Morphing

## The component

```php
<?php

use Livewire\Component;

class Counter extends Component
{
    public $count = 1;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

```blade
<div>
    <button wire:click="increment">Increment</button>

    <span>{{ $count }}</span>
</div>
```

## Rendering the component

```blade
<livewire:counter />
```

```php
<?php echo Livewire::mount('counter'); ?>
```

```php
public function mount($name)
{
    $class = Livewire::getComponentClassByName();

    $component = new $class;

    $id = str()->random(20);

    $component->setId($id);

    $data = $component->getData();

    $view = $component->render();

    $html = $view->render($data);

    $snapshot = [
        'data' => $data,
        'memo' => [
            'id' => $component->getId(),
            'name' => $component->getName(),
        ]
    ];

    return Livewire::embedSnapshotInsideHtml($html, $snapshot);
}
```

```blade
<div wire:id="123456789" wire:snapshot="{ data: { count: 0 }, memo: { 'id': '123456789', 'name': 'counter' }">
    <button wire:click="increment">Increment</button>

    <span>1</span>
</div>
```

## JavaScript initialization

```js
let el = document.querySelector('wire\\:id')

let id = el.getAttribute('wire:id')
let jsonSnapshot = el.getAttribute('wire:snapshot')
let snapshot = JSON.parse(jsonSnapshot)

let component = { id, snapshot }

walk(el, el => {
    el.hasAttribute('wire:click') {
        let action = el.getAttribute('wire:click')

        el.addEventListener('click', e => {
            updateComponent(el, component, action)
        })
    }
})

function updateComponent(el, component, action) {
    let response fetch('/livewire/update', {
        body: JSON.stringify({
            "snapshot": snapshot,
            "calls": [
                ["method": action, "params": []],
            ]
        })
    })

    // To be continued...
}
```

## Receiving an update

```php
Route::post('/livewire/update', function () {
    $snapshot = request('snapshot');
    $calls = request('calls');

    $component = Livewire::fromSnapshot($snapshot);

    foreach ($calls as $call) {
        $component->{$call['method']}(...$call['params']);
    }

    [$html, $snapshot] = Livewire::snapshot($component);

    return [
        'snapshot' => $snapshot,
        'html' => $html,
    ];
});
```

## Handling an update

```js
function updateComponent(el, component, action) {
    fetch('/livewire/update', {
        body: JSON.stringify({
            "snapshot": snapshot,
            "calls": [
                ["method": action, "params": []],
            ]
        })
    }).then(i => i.json()).then(response => {
        let { html, snapshot } = response

        component.snapshot = snapshot

        el.outerHTML = html
    })
}
```

