# Custom JavaScript

<div title="Warning"><div title="Warning__content">

This is an experimental feature. Something more robust is in development.
</div></div>

Livewire's goal is to remove as much JS pain as possible. However, there are some cases where JS is the right tool for the job.

To package JavaScript up with a component, just add a `<script>` tag inside the component's root div. For example:

```html
<div>
    <script>alert('Hello World');</script>
</div>
```
