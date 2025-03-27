import { sendRequest } from "@/request"

/**
 * The RequestPool contains a list of commits to be sent to the server...
 */
export class RequestPool {
    constructor() {
        this.commits = new Set
    }

    add(commit) {
        this.commits.add(commit)
    }

    delete(commit) {
        this.commits.delete(commit)
    }

    hasCommitFor(component) {
        return !! this.findCommitByComponent(component)
    }

    findCommitByComponent(component) {
        // Determine if this pool already has a commit for this component...
        for (let [idx, commit] of this.commits.entries()) {
            if (commit.component === component) return commit
        }
    }

    // Determine if a commit should be added to this pool or isolated into its own...
    shouldHoldCommit(commit) {
        return ! commit.isolate
    }

    empty() {
        return this.commits.size === 0
    }

    async send() {
        this.prepare()

        // Get the pool payload
        let [commitPayloads, succeed, fail] = this.payload()

        // If all commits became stale and we have no payloads to send,
        // resolve immediately without sending a request
        if (commitPayloads.length === 0) {
            return Promise.resolve()
        }

        // Send this pool of commits to the server and let the commits
        // Manage their own response actions...
        await sendRequest(this)
    }

    prepare() {
        // Give each commit a chance to do any last-minute prep
        // before being sent to the server.
        this.commits.forEach(i => i.prepare())
    }

    payload() {
        // Extract a request payload from each of the commits in this pool...
        let commitPayloads = []

        // Collect success and failure callbacks to be used inside aggregated callbacks...
        let successReceivers = []
        let failureReceivers = []

        // Track the components from stale commits to ensure their loading states are cleared
        let componentsWithStaleCommits = []

        this.commits.forEach(commit => {
            // For stale commits, track their components for loading state cleanup
            if (commit.stale) {
                componentsWithStaleCommits.push(commit.component)
                return
            }

            let [payload, succeed, fail] = commit.toRequestPayload()

            commitPayloads.push(payload)
            successReceivers.push(succeed)
            failureReceivers.push(fail)
        })

        // Aggregate the success and failure callbacks for individual commits
        // into something that can be called singularly...
        let succeed = components => {
            // Process regular responses
            successReceivers.forEach(receiver => receiver(components.shift()))

            // Ensure loading states are cleaned up for stale components
            if (componentsWithStaleCommits.length > 0) {
                componentsWithStaleCommits.forEach(component => {
                    if (component && component.effects) {
                        // Make sure loading states are cleared
                        component.loadingStates && component.loadingStates.forEach(state => {
                            state.finish()
                        })
                    }
                })
            }
        }

        let fail = () => {
            failureReceivers.forEach(receiver => receiver())

            // Ensure loading states are cleaned up for stale components on failure too
            if (componentsWithStaleCommits.length > 0) {
                componentsWithStaleCommits.forEach(component => {
                    if (component && component.effects) {
                        // Make sure loading states are cleared
                        component.loadingStates && component.loadingStates.forEach(state => {
                            state.finish()
                        })
                    }
                })
            }
        }

        return [ commitPayloads, succeed, fail ]
    }
}
