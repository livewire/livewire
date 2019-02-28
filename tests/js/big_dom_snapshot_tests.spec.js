import { fireEventAndMakeServerRespondWithDom } from './utils'

test('test basic click', () => {
    document.body.innerHTML = `<div id="app">
        <div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button></button>
            </form>
        </div>
    </div>`

    fireEventAndMakeServerRespondWithDom('button', 'click', document.querySelector(`[wire\\:id="componentA"]`).outerHTML)

    expect(document.body.outerHTML).toMatchSnapshot();
})

test('test adding element', () => {
    document.body.innerHTML = `<div id="app">
        <div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button></button>
            </form>
        </div>
    </div>`

    fireEventAndMakeServerRespondWithDom('button', 'click',
        `<div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <span>Something updated</span>
                <button>hey</button>
            </form>
        </div>`
    )

    expect(document.body.outerHTML).toMatchSnapshot();
})

test('test adding element with sibling components', () => {
    document.body.innerHTML = `<div id="app">
        <div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button></button>
            </form>
        </div>

        <div wire:id="componentB" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button></button>
            </form>
        </div>
    </div>`

    fireEventAndMakeServerRespondWithDom('[wire:id="componentB"] button', 'click',
        `<div wire:id="componentB" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button>button</button>
                <div>something added</div>
            </form>
        </div>`
    )

    expect(document.body.outerHTML).toMatchSnapshot();
})

test('test adding element inside nested component', () => {
    document.body.innerHTML = `<div id="app">
        <div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button></button>
            </form>

            <div wire:id="componentB" wire:serialized="empty">
                <form wire:submit="doSomething" wire:ref="submitEl">
                    <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                    <button></button>
                </form>
            </div>
        </div>
    </div>`

    fireEventAndMakeServerRespondWithDom('[wire:id="componentB"] button', 'click',
        `<div wire:id="componentB" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button>button</button>
                <div>something added</div>
            </form>
        </div>`
    )

    expect(document.body.outerHTML).toMatchSnapshot();
})

test('test adding element outside nested component', () => {
    document.body.innerHTML = `<div id="app">
        <div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button></button>
            </form>

            <div wire:id="componentB" wire:serialized="empty">
                <form wire:submit="doSomething" wire:ref="submitEl">
                    <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                    <button></button>
                </form>
            </div>
        </div>
    </div>`

    fireEventAndMakeServerRespondWithDom('[wire:id="componentA"] button', 'click',
        `<div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button>button</button>
                <div>something added</div>
            </form>

            <div wire:id="componentB" wire:serialized="empty">
            </div>
        </div>`
    )

    expect(document.body.outerHTML).toMatchSnapshot();
})

test('test adding nested component', () => {
    document.body.innerHTML = `<div id="app">
        <div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button>button</button>
            </form>
        </div>
    </div>`

    fireEventAndMakeServerRespondWithDom('[wire:id="componentA"] button', 'click',
        `<div wire:id="componentA" wire:serialized="empty">
            <form id="yih" wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button>button</button>

                <div wire:id="componentB" wire:serialized="empty">
                    <div id="yo" wire:submit="doSomething" wire:ref="submitEl">
                        <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                    </div>
                </div>
            </form>
        </div>`
    )

    expect(document.body.outerHTML).toMatchSnapshot();
})

test('test removing nested component', () => {
    document.body.innerHTML = `<div id="app">
        <div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button></button>
            </form>

            <div wire:id="componentB" wire:serialized="empty">
                <form wire:submit="doSomething" wire:ref="submitEl">
                    <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                    <button></button>
                </form>
            </div>
        </div>
    </div>`

    fireEventAndMakeServerRespondWithDom('[wire:id="componentA"] button', 'click',
        `<div wire:id="componentA" wire:serialized="empty">
            <form wire:submit="doSomething" wire:ref="submitEl">
                <div id="spinner" class="hidden" wire:loading="submitEl"></div>
                <button>button</button>
                <div>something added</div>
            </form>
        </div>`
    )

    expect(document.body.outerHTML).toMatchSnapshot();
})
