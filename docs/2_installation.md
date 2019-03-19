# Installation
Livewire has both a PHP component AND a Javascript component. You need to make sure both are available in your project before you can use it.

## Install via composer
`composer require calebporzio/livewire`

## Include Javascript via snippet
To get started quickly, you can include the following snippet at the end of the `<body>` in your html page:

```html
        ...
        <script>{!! Livewire::scripts() !!}</script>
        <script>Livewire.start()</script>
    </body>
</html>
```

## Include Javascript via NPM
If you have a more sophistocated javascript build setup, you can install and import Livewire via NPM.

`npm install laravel-livewire --save-dev`

```js
import Livewire from 'laravel-livewire'

Livewire.start()
```
