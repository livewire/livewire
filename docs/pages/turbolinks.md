# SPA Mode

While Livewire makes individual pages feal smooth, it doesn't give transitions between pages the same love. Livewire plans to support this functionality out of the box, but for now, it is recommended to use Turbolinks.

Fortunately, getting Turbolinks to play nicely with Livewire is simple. Let's walk through it.

Checkout the [Turbolinks documentation](https://github.com/turbolinks/turbolinks) for installation instructions.

Now, add the following JS in a script tag at the bottom of the page, or in your `app.js` file.

```js
Turbolinks.start()

document.addEventListener('turbolinks:load', () => {
    if (! window.livewire) {
        window.livewire = new Livewire()
    } else {
        window.livewire.restart()
    }
})
```

And that's it! This little snippet tells Turbolinks to reload Livewire everytime a new page is visited or returned to.
