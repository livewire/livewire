import connection from './connection.js'
import roots from './roots.js'
import handleMorph from './handleMorph.js';

connection.init()
    .then(() => {
        connection.onMessage((payload) => {
            handleMorph(payload.component, payload.dom, payload.dirtyInputs)
        })

        roots.init()
    })
