# Installation
Livewire has both a PHP component AND a Javascript component. You need to make sure both are available in your project before you can use it.

## Install via composer
`composer require calebporzio/livewire`

## Include JavaScript portion

There are two methods available to include the JavaScript portion of Livewire.

### Method 1: Include via snippet
To get started quickly, you can include the following snippet at the end of the `<body>` in your html page:

```html
        ...
        {!! Livewire::scripts() !!}
    </body>
</html>
```

### Method 2: Include via NPM
If you have a more sophistocated javascript build setup, you can install and import Livewire via NPM.

`npm install laravel-livewire --save-dev`

```js
import Livewire from 'laravel-livewire'

Livewire.start()
```
