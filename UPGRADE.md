# Upgrade guide for 3.x -> 4.x

## Method signature changes
- `mount($name, $params = [], $key = null)` -> `mount($name, $params = [], $key = null, $slots = [])`
- `dispatch()`

## Pre-release questions
- Should we make `$refs.modal.dispatch('close')` be { bubbles: false } by default when $wire is accessed through `$ref` or `$parent`? Instead of needing `dispatchSelf()`
