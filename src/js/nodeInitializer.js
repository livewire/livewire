import renameme from './renameme'
import store from './store'
const prefix = require('./prefix.js')()
import { closestByAttribute, getAttribute, extractDirectivesModifiersAndValuesFromEl } from './domHelpers'
import ElementDirectives from './ElementDirectives';

export default class NodeInitializer {
    constructor(connection) {
        this.connection = connection.init()
    }

    findByEl(el) {
        return store.componentsById[this.getRootIdFromEl(el)]
    }

    getRootIdFromEl(el) {
        return getAttribute(closestByAttribute(el, 'root-id'), 'root-id')
    }

    initialize(node) {
        // Make sure it's an ElementNode and not a TextNode or something
        if (typeof node.hasAttribute !== 'function') return

        const directives = new ElementDirectives(node)

        if (directives.has('click')) {
            if (directives.get('click').modifiers.includes('min')) {
                var waitTime = Number((directives.get('click').modifiers.filter(item => item.match(/.*ms/))[0] || '0ms').match('(.*)ms')[1])
            } else {
                var waitTime = 0
            }

            renameme.attachClick(node, (method, params, el) => {
                if (method === '$emit') {
                    let eventName
                    [eventName, ...params] = params
                    this.connection.sendEvent(eventName, params, this.findByEl(el))
                    return
                }

                this.connection.sendMethod(method, params, this.findByEl(el), el.getAttribute(`${prefix}:ref`), waitTime)
            }, directives.get('click').modifiers, directives.get('click').value)
        }

        if (directives.has('loading')) {
            const ref = directives.get('loading').value
            const root = this.findByEl(node);
            root.addLoadingEl(node, ref)
        }

        if (directives.has('submit')) {
            renameme.attachSubmit(node, (method, params, el) => {
                const root = this.findByEl(el);

                this.connection.sendMethod(method, [params], root, el.getAttribute(`${prefix}:ref`))
            })
        }

        if (directives.has('keydown')) {
            renameme.attachEnter(node, (method, params, el) => {
                this.connection.sendMethod(method, params, this.findByEl(el))
            }, directives.get('keydown').modifiers, directives.get('keydown').value)
        }

        if (directives.has('model')) {
            renameme.attachSync(node, (model, el) => {
                const value = el.type === 'checkbox'
                    ? el.checked
                    : el.value

                if (directives.get('model').modifiers.includes('lazy')) {
                    this.findByEl(el).queueSyncInput(model, value)
                } else {
                    this.connection.sendSync(model, value, this.findByEl(el))
                }
            })
        }
    }
}
