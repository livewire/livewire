import listenerManager from './EventListenerManager'
import store from './Store'
const prefix = require('./Prefix.js')()
import { closestByAttribute, getAttribute, extractDirectivesModifiersAndValuesFromEl } from './DomHelpers'
import ElementDirectives from './ElementDirectives';

export default class NodeInitializer {
    constructor(connection) {
        this.connection = connection.init()
    }

    componentByEl(el) {
        return store.componentsById[this.getComponentIdFromEl(el)]
    }

    getComponentIdFromEl(el) {
        return getAttribute(closestByAttribute(el, 'root-id'), 'root-id')
    }

    initialize(node) {
        // Make sure it's an ElementNode and not a TextNode or something
        if (typeof node.hasAttribute !== 'function') return

        const directives = new ElementDirectives(node)

        if (directives.has('click')) {
            if (directives.get('click').modifiers.includes('min')) {
                var waitTime = Number(
                    (directives.get('click').modifiers.filter(item => item.match(/.*ms/))[0] || '0ms').match('(.*)ms')[1]
                )
            } else {
                var waitTime = 0
            }

            listenerManager.attachClick(node, (method, params, el) => {
                if (method === '$emit') {
                    let eventName
                    [eventName, ...params] = params
                    this.connection.sendEvent(eventName, params, this.componentByEl(el))
                    return
                }

                this.connection.sendMethod(method, params, this.componentByEl(el), el.getAttribute(`${prefix}:ref`), waitTime)
            }, directives.get('click').modifiers, directives.get('click').value)
        }

        if (directives.has('submit')) {
            listenerManager.attachSubmit(node, (method, params, el) => {
                const component = this.componentByEl(el);

                this.connection.sendMethod(method, [params], component, el.getAttribute(`${prefix}:ref`))
            })
        }

        if (directives.has('keydown')) {
            listenerManager.attachEnter(node, (method, params, el) => {
                this.connection.sendMethod(method, params, this.componentByEl(el))
            }, directives.get('keydown').modifiers, directives.get('keydown').value)
        }

        if (directives.has('model')) {
            listenerManager.attachSync(node, (model, el) => {
                const value = el.type === 'checkbox'
                    ? el.checked
                    : el.value

                if (directives.get('model').modifiers.includes('lazy')) {
                    this.componentByEl(el).queueSyncInput(model, value)
                } else {
                    this.connection.sendSync(model, value, this.componentByEl(el))
                }
            })
        }
    }
}
