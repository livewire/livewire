# Upgrade guide for 3.x -> 4.x

## Method signature changes
- `mount($name, $params = [], $key = null)` -> `mount($name, $params = [], $key = null, $slots = [])`
- `stream($name, $content, $replace = false)` -> `stream($content, $replace, $name)`

## Config changes
- New `livewire.component_locations` to define view-based component locations
- New `livewire.component_namespaces` to define view-based component namespaces
- `livewire.layout` -> `livewire.component_layout` (Was 'components.layouts.app'. Now is 'layouts::app')
- `livewire.lazy_placeholder` -> `livewire.component_placeholder`
- `make_command.type` ('sfc', 'mfc', or 'class')
- `make_command.emoji` (weather to use the emoji prefix or not)

## Pre-release questions
- Should we make `$refs.modal.dispatch('close')` be { bubbles: false } by default when $wire is accessed through `$ref` or `$parent`? Instead of needing `dispatchSelf()`
