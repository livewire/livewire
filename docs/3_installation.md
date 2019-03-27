# Installation
Livewire has both a PHP component AND a Javascript component. You need to make sure both are available in your project before you can use it.

## Install via composer
```bash
> composer require calebporzio/livewire
```

## Include JavaScript portion

There are two methods available to include the JavaScript portion of Livewire.

### Method 1: Include via snippet
To get started quickly, you can include the following snippet at the end of the `<body>` in your html page. It is recommended that you put this in a layout file such as `resources/views/layouts/app.blade.php`. You can create this layout file in a new laravel project by executing `artisan make:auth`.

<div title="Component"><div title="Component__class"><div char="fade">

```html
    ...
```
</div>

```php
    {!! Livewire::scripts() !!}
```
<div char="fade">

```html
</body>
</html>
```
</div></div></div>

### Method 2: Include via NPM
If you have a more sophistocated javascript build setup, you can install and import Livewire via NPM.

```bash
> npm install laravel-livewire --save-dev
```

```js
import Livewire from 'laravel-livewire'

Livewire.start()
```
