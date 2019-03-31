<?php

/**
 * Declare the pages.
 */
collect([
    'Quickstart' => 'pages/quickstart.md',
    'Installation' => 'pages/installation.md',
    'Component Basics' => 'pages/component_basics.md',
    'Rendering Components' => 'pages/rendering_components.md',
    'Data Binding' => 'pages/data_binding.md',
    'Event Actions' => 'pages/event_actions.md',
    'Input Validation' => 'pages/validation.md',
    'Lifecycle Hooks' => 'pages/lifecycle_hooks.md',
    'Loading States' => 'pages/loading.md',
    'CSS Transitions' => 'pages/transitions.md',
    'Testing' => 'pages/testing.md',
    'Custom JavaScript' => 'pages/custom_javascript.md',
    'SPA Mode' => 'pages/turbolinks.md',
])
/**
 * Prepare the page data.
 */
->map(function ($file, $title) {
    preg_match('/\/(.*).md/', $file, $matches);

    return [
        'title' => $title,
        'path' => '/docs/' . kebab_case($matches[1]),
        'contents' => file_get_contents(__DIR__ . str_start($file, '/')),
    ];
})
/**
 * Do a dirty thing: make the current collection available inside the next collection method.
 */
->tap(function ($carry) use (&$collection) { $collection = $carry; })
/**
 * Register routes.
 */
->each(function ($page) use ($collection) {
    app('router')->get($page['path'], function () use ($page, $collection) {
        return view('template', [
            'title' => $page['title'],
            'content' => $page['contents'],
            'links' => $collection->pluck('title', 'path'),
        ]);
    });
});
