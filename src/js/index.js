import WebSocketConnection from './WebSocketConnection'
import HttpConnection from './HttpConnection'
import Backend from './Backend'
import renameme from './renameme'
import RootManager from './RootManager'
const prefix = require('./prefix.js')()
const morphdom = require('morphdom');

const backend = new Backend(new WebSocketConnection)

const roots = new RootManager(backend)

if (roots.count) {
    backend.init({
        onConnect() {
            roots.init()
        },

        onMessageReceived(payload) {
            const component = payload.component;
            const dom = payload.dom;
            const formsInNeedOfRefresh = payload.refreshForms;

            morphdom(roots.find(component).el.firstElementChild, dom, {
                onBeforeElChildrenUpdated(from, to) {
                    // This allows nesting components
                    if (from.hasAttribute(`${prefix}:root`)) {
                        return false
                    }
                },

                onBeforeElUpdated(el) {
                    // This will need work. But is essentially "input persistance"
                    const isInput = (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')

                    if (isInput) {
                        if (el.type === 'submit') {
                            return true
                        }

                        const isInForm = el.hasAttribute(`${prefix}:form.sync`)

                        if (isInForm) {
                            const formName = el.closest(`[${prefix}\\:form]`).getAttribute(`${prefix}:form`)
                            if (Array.from(formsInNeedOfRefresh).includes(formName)) {
                                return true
                            } {
                                return false
                            }
                        }
                        return false
                    }
                },

                onNodeAdded(node) {
                    if (typeof node.hasAttribute !== 'function') {
                        return
                    }

                    if (roots.isRoot(node)) {
                        roots.add(node)
                    } else {
                        initializeNode(node)
                    }
                },
            });
        }
    })
}

function sendMethod(method, params, el) {
    backend.message({
        event: 'fireMethod',
        payload: {
            method,
            params,
        },
        component: el.closest(`[${prefix}\\:root]`).getAttribute(`${prefix}:root`)
    })
}

function sendSync(model, el) {
    backend.message({
        event: 'sync',
        payload: { model, value: el.value },
        component: el.closest(`[${prefix}\\:root]`).getAttribute(`${prefix}:root`)
    })
}

function sendFormInput(form, input, el) {
    backend.message({
        event: 'form-input',
        payload: { form, input, value: el.value },
        component: el.closest(`[${prefix}\\:root]`).getAttribute(`${prefix}:root`)
    })
}

function initializeNode(node) {
    if (node.hasAttribute(`${prefix}:click`)) {
        renameme.attachClick(node, (method, params, el) => {
            sendMethod(method, params, el)
        })
    }

    if (node.hasAttribute(`${prefix}:form.sync`)) {
        renameme.attachFormInput(node, (form, input, el) => {
            sendFormInput(form, input, el)
        })
    }

    if (node.hasAttribute(`${prefix}:submit`)) {
        renameme.attachSubmit(node, (method, params, el) => {
            sendMethod(method, [params], el)
        })
    }

    if (node.hasAttribute(`${prefix}:keydown.enter`)) {
        renameme.attachEnter(node, (method, params, el) => {
            sendMethod(method, params, el)
        })
    }

    if (node.hasAttribute(`${prefix}:sync`)) {
        renameme.attachSync(node, (model, el) => {
            sendSync(model, el)
        })
    }
}
