import interceptorRegistry from '@/v4/interceptors/interceptorRegistry.js'
import messageBroker from '@/v4/requests/messageBroker.js'

export default class Action {
    context = {}

    constructor(component, method, params = [], el = null, directive = null) {
        this.component = component
        this.method = method
        this.params = params
        this.el = el
        this.directive = directive
    }

    addContext(context) {
        this.context = {...this.context, ...context}
    }

    fire() {
        let context = messageBroker.pullContext(this.component)

        if (context.el) {
            this.el = context.el

            delete context.el
        }

        if (context.directive) {
            this.directive = context.directive

            delete context.directive
        }

        this.addContext(context)

        interceptorRegistry.fire(this)

        return messageBroker.addAction(this.component, this.method, this.params, this.context)
    }
}
