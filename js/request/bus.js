import { trigger } from '@/hooks'
import { RequestPool } from './pool'
import { Commit } from './commit'

/**
 * This bus manages the pooling of multiple commits and sending
 * those pools of commits to the server...
 */
export class CommitBus {
    constructor() {
        // A list of loose, un-pooled, commits ready to be pooled and sent to the server...
        this.commits = new Set

        // A list of commit pools currently out to the server...
        this.pools = new Set
    }

    add(component) {
        // If this component already has a commit, leave it, otherwise,
        // create a new commit and add it to the list...
        let commit = this.findCommitOr(component, () => {
            let newCommit = new Commit(component)

            this.commits.add(newCommit)

            return newCommit
        })

        // Buffer the sending of a pool for 5ms to account for UI interactions
        // that will trigger multiple events within a few milliseconds of each other.
        // For example, clicking on a button that both unfocuses a field and registers a mousedown...
        bufferPoolingForFiveMs(commit, () => {
            // If this commit is already in a pool, leave it be...
            let pool = this.findPoolWithComponent(commit.component)

            if (! pool) {
                // If it's not, create a new pool or add it to an existing one and trigger a network request...
                this.createAndSendNewPool()
            }
        })

        return commit
    }

    findCommitOr(component, callback) {
        for (let [idx, commit] of this.commits.entries()) {
            if (commit.component === component) {
                return commit
            }
        }

        return callback()
    }

    findPoolWithComponent(component) {
        for (let [idx, pool] of this.pools.entries()) {
            if (pool.hasCommitFor(component)) return pool
        }
    }

    createAndSendNewPool() {
        // Allow features like "reactive properties" to initiate associated
        // commits before those commits are pooled for a network request...
        trigger('commit.pooling', { commits: this.commits })

        // Split commits up across one or multiple pools to be sent as seperate network requests...
        let pools = this.corraleCommitsIntoPools()

        // Clear all commits in the queue now that they're in pools...
        this.commits.clear()

        trigger('commit.pooled', { pools })

        // Go through each pool and...
        pools.forEach(pool => {
            // Ignore empty pools (if other parts of the system have moved commits around different pools)...
            if (pool.empty()) return

            // Add it to the list of pending pools...
            this.pools.add(pool)

            // Send it's payload along to the server...
            pool.send().then(() => {
                // When it comes back, remove it from the list...
                this.pools.delete(pool)

                // Trigger another pooling phase in case commits have
                // been added while the current request was out...
                this.sendAnyQueuedCommits()
            })
        })
    }

    corraleCommitsIntoPools() {
        let pools = new Set

        // Go through each commit and assess wether it should be bundled
        // with other commits or sperated into it's own pool (network request)...
        for (let [idx, commit] of this.commits.entries()) {
            let hasFoundPool = false

            // If an existing pool wants to claim a commit, let it...
            pools.forEach(pool => {
                if (pool.shouldHoldCommit(commit)) {
                    pool.add(commit)

                    hasFoundPool = true
                }
            })

            // Otherwise, create a new pool and seed it with this commit...
            if (! hasFoundPool) {
                let newPool = new RequestPool

                newPool.add(commit)

                pools.add(newPool)
            }
        }

        return pools
    }

    sendAnyQueuedCommits() {
        if (this.commits.size > 0) {
            this.createAndSendNewPool()
        }
    }
}

let buffersByCommit = new WeakMap

function bufferPoolingForFiveMs(commit, callback) {
    if (buffersByCommit.has(commit)) return

    buffersByCommit.set(commit, setTimeout(() => {
        callback()

        buffersByCommit.delete(commit)
    }, 5))
}
