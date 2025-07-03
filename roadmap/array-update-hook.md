Currently when a value in an array is updated, the updated hook only shows the delta of the changes.

This is unexpected and at time the value can actually be `__rm__`.

This should be changed to match v2 where by the updated hook returns the full value of the array instead.

See my failing test PR for full details of the issue and possible options https://github.com/livewire/livewire/pull/8235

Other issue https://github.com/livewire/livewire/discussions/7101
