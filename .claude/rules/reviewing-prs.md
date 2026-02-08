# Reviewing PRs

- Before accepting docs or workaround PRs, ask: "Could a code change eliminate the need for this workaround entirely?" A 10-line code fix beats 60 lines of workaround docs.
- Research upstream/peer libraries before accepting limitations at face value. Check if the data already exists and just isn't surfaced.
- Question naming for ambiguity — names should be immediately self-evident. If a name could be confused with something else, it's wrong (e.g. `wire:sort:id` -> `wire:sort:group-id`).
