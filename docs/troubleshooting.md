* [Cloudflare Combatability Issues](#cloudflare-combatability-issues)
    * [Symptoms](#cloudflare-symptoms)
    * [Cures](#cloudflare-cures)
* [Contributing to Troubleshooting](/docs/contribution-guide)


## Cloudflare Compatibility Issues {#cloudflare-compatibility-issues}

When using Livewire in a production environment, you might encounter issues that stem from Cloudflare's Rocket Loader and HTML minification features. These issues can manifest as the page becoming unresponsive or errors indicating that a component is not found.

### Symptoms {#cloudflare-symptoms}
* Page becomes unresponsive after using `wire:navigate`.
* Errors like "Component not found: {component_ID}".

### Cures {#cloudflare-cures}
* **Disable Rocket Loader:** Go to your Cloudflare dashboard, navigate to the "Speed" section, and turn off Rocket Loader. This feature can interfere with the execution order of JavaScript, which is crucial for Livewire's operation.
* **Disable HTML Minification:** In the Cloudflare dashboard under the "Optimization" section, turn off HTML minification. This will prevent alteration or minification of Livewire's inline scripts, which can lead to operational errors.