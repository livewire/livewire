import { on } from '@/hooks'

// Ensure that all child components with reactive props (even deeply nested)
// are included in the network request...
on('commit.pooling', ({ commits }) => {
    commits.forEach(commit => {
        let component = commit.component

        getDeepChildrenWithBindings(component, child => {
            child.$wire.$commit()
        })
    })
})

// Ensure that related components are pooled together, even if they chose
// to be isolated normally...
on('commit.pooled', ({ pools }) => {
    let commits = getPooledCommits(pools)

    commits.forEach(commit => {
        let component = commit.component

        getDeepChildrenWithBindings(component, child => {
            colocateCommitsByComponent(pools, component, child)
        })
    })
})

function getPooledCommits(pools) {
    let commits = []

    pools.forEach(pool => {
        pool.commits.forEach(commit => {
            commits.push(commit)
        })
    })

    return commits
}

function colocateCommitsByComponent(pools, component, foreignComponent) {
    let pool = findPoolWithComponent(pools, component)

    let foreignPool = findPoolWithComponent(pools, foreignComponent)

    let foreignCommit = foreignPool.findCommitByComponent(foreignComponent)

    // Delete needs to come before add in case there are the same pool...
    foreignPool.delete(foreignCommit)

    pool.add(foreignCommit)

    // Cleanup empty pools...
    pools.forEach(pool => {
        if (pool.empty()) pools.delete(pool)
    })
}

function findPoolWithComponent(pools, component) {
    for (let [idx, pool] of pools.entries()) {
        if (pool.hasCommitFor(component)) return pool
    }
}

function getDeepChildrenWithBindings(component, callback) {
    getDeepChildren(component, child => {
        if (hasReactiveProps(child) || hasWireModelableBindings(child)) {
            callback(child)
        }
    })
}

function hasReactiveProps(component) {
    let meta = component.snapshot.memo
    let props = meta.props

    return !! props
}

function hasWireModelableBindings(component) {
    let meta = component.snapshot.memo
    let bindings = meta.bindings

    return !! bindings
}

function getDeepChildren(component, callback) {
    component.children.forEach(child => {
        callback(child)

        getDeepChildren(child, callback)
    })
}
