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

        this.commits.forEach(commit => {
            let [payload, succeed, fail] = commit.toRequestPayload()

            commitPayloads.push(payload)
            successReceivers.push(succeed)
            failureReceivers.push(fail)
        })

        // Aggregate the success and failure callbacks for individual commits
        // into something that can be called singularly...
        let succeed = components => successReceivers.forEach(receiver => receiver(components.shift()))

        let fail = () => failureReceivers.forEach(receiver => receiver())

        return [ commitPayloads, succeed, fail ]
    }
}
