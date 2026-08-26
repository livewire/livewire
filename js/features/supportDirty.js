import { on } from '@/hooks'

// The server can declare (via `$this->rebaseline()`) that the state it just returned
// is the component's new "saved" state. Snapshots are merged before effects are
// processed, so `component.canonical` already holds that state here...
on('effect', ({ component, effects }) => {
    if (! effects['rebaseline']) return

    component.rebaseline()
})
