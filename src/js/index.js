import connection from './connection.js'
import roots from './roots.js'
import handleMorph from './handleMorph.js';
import http from './http.js';

// First, get connected to the backend via WebSockets or Http,
// then, initialize all the Livewire components on the page.
connection.init()
    .then(() => {
        roots.init()
    })
