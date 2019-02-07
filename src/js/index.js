import connection from './connection.js'
import roots from './roots.js'
import handleMorph from './handleMorph.js';
import http from './http.js';

connection.init()
    .then(() => {
        roots.init()
    })
