# Turbolinks

While Livewire makes individual pages feal smooth, it doesn't give transitions between pages the same love. For this, we have Turbolinks.

Turbolinks is a handy tool that hijacks full page reloads (like clicking a link to another page) and processes them in the background, patching the updated DOM into the current page. It very easily makes a traditional server-side application, feel like a single-page application.

Fortunately, getting Turbolinks to play nicely with Livewire is simple. Let's walk through it.

Checkout the [Turbolinks documentation](https://github.com/turbolinks/turbolinks) for installation instructions.

Now, replace your `Livewire.start()` JS code with the following:

```js
document.addEventListener('turbolinks:load', function () {
    Livewire.stop()
    Livewire.start()
})
```

And that's it! This little snippet tells Turbolinks to reload Livewire everytime a new page is visited or returned to.
