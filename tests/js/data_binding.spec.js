import { fireEvent, wait, waitForDomChange } from 'dom-testing-library'
import testHarness from './fixtures/test_harness'

test('properties sync on input change', async () => {
    var payload
    testHarness.mount({
        dom: '<input wire:model="foo">',
        requestInterceptor: i => payload = i,
    })

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual('bar')
    })
})

test('nested properties sync on input change', async () => {
    var payload
    testHarness.mount({
        dom: '<input wire:model="foo.one.two">',
        initialData: { foo: [] },
        requestInterceptor: i => payload = i,
    })

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo.one.two')
        expect(payload.actionQueue[0].payload.value).toEqual('bar')
    })
})

test('properties are lazy synced when action is fired', async () => {
    var payload
    testHarness.mount({
        dom: '<input wire:model.lazy="foo"><button wire:click="onClick"></button>',
        requestInterceptor: i => payload = i,
    })

    fireEvent.change(document.querySelector('input'), { target: { value: 'bar' }})

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual('bar')
    })
})

test('textarea data binding with class change works as expected and doesn\'t wipe its value', async () => {
    testHarness.mount({
        dom: '<textarea wire:model="foo" class="foo"></textarea>',
        response: {
            dom: '<textarea wire:model="foo" class="foo bar"></textarea>',
        }
    })

    fireEvent.input(document.querySelector('textarea'), { target: { value: 'bar' }})

    await wait(() => {
        expect(document.querySelector('textarea').value).toEqual('bar')
        expect(document.querySelector('textarea').classList.contains('bar')).toBeTruthy()
    })
})

test('input element value doesnt change unless property is marked as dirty', async () => {
    testHarness.mount({
        dom: '<input wire:model="foo" value="">',
        response: {
            dom: '<input wire:model="foo" value="bar"><button>Im here to trigger dom change</button>',
        }
    })

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await waitForDomChange(document.body, () => {
        expect(document.querySelector('input').value).toEqual('baz')
    })

    testHarness.mount({
        dom: '<input wire:model="foo" value="">',
        response: {
            dom: '<input wire:model="foo" value="bar"><button>Im here to trigger dom change</button>',
            dirtyInputs: ['foo'],
        }
    })

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await waitForDomChange(document.body, () => {
        expect(document.querySelector('input').value).toEqual('bar')
    })
})

test('input element value doesnt change, but other attributes do when not marked as dirty', async () => {
    testHarness.mount({
        dom: '<input wire:model="foo" class="foo" value="">',
        response: {
            dom: '<input wire:model="foo" class="foo bar" value="bar">',
            dirtyInputs: []
        }
    })

    document.querySelector('input').focus()
    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await wait(() => {
        expect(document.querySelector('input').value).toEqual('baz')
        expect(document.querySelector('input').classList.contains('bar')).toBeTruthy()
    })
})

test('input element value attribute is automatically updated if present in returned dom', async () => {
    testHarness.mount({
        dom: '<input wire:model="foo"><button wire:click="onClick"></button>',
        response: {
            dom: '<input wire:model="foo"><button wire:click="onClick"></button>',
            data: { foo: 'bar' },
            dirtyInputs: ['foo'],
        }
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('input').value).toBe('bar')
    })
})

test('input element value is automatically updated', async () => {
    testHarness.mount({
        dom: '<input wire:model="foo">',
        initialData: { foo: 'bar' },
    })

    await wait(() => {
        expect(document.querySelector('input').value).toBe('bar')
    })
})

test('textarea element value is automatically updated', async () => {
    testHarness.mount({
        dom: '<textarea wire:model="foo"></textarea>',
        initialData: { foo: 'bar' },
    })

    await wait(() => {
        expect(document.querySelector('textarea').value).toBe('bar')
    })
})

test('checkbox element value attribute is automatically added if not present in the initial dom', async () => {
    testHarness.mount({
        dom: '<input type="checkbox" wire:model="foo">',
        initialData: { foo: true },
    })

    await wait(() => {
        expect(document.querySelector('input').checked).toBeTruthy()
    })
})

test('checkboxes bound to empty array arent checked', async () => {
    testHarness.mount({
        dom: `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        initialData: { foo: [] },
    })
    expect(document.querySelector('#a').checked).toBeFalsy()
})

test('checkboxes bound to an array containing value are checked', async () => {
    testHarness.mount({
        dom: `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        initialData: { foo: ['a'] },
    })
    expect(document.querySelector('#a').checked).toBeTruthy()
})

test('checkboxes bound to an array containing a numeric value are checked', async () => {
    testHarness.mount({
        dom: `<input id="a" type="checkbox" wire:model="foo" value="2">`,
        initialData: { foo: [2] },
    })
    expect(document.querySelector('#a').checked).toBeTruthy()
})

test('checkboxes bound to an array containing a different value are not', async () => {
    testHarness.mount({
        dom: `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        initialData: { foo: ['b'] },
    })
    expect(document.querySelector('#a').checked).toBeFalsy()
})

test('checking a checkbox bound to an array will toggle its value inside the array', async () => {
    var payload
    testHarness.mount({
        dom: `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        initialData: { foo: [] },
        requestInterceptor: i => payload = i,
    })

    fireEvent.click(document.querySelector('#a'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual(['a'])
    })

    var payload
    testHarness.mount({
        dom: `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        initialData: { foo: ['a'] },
        requestInterceptor: i => payload = i,
    })

    fireEvent.click(document.querySelector('#a'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual([])
    })
})

test('select element options are automatically selected', async () => {
    testHarness.mount({
        dom: '<select wire:model="foo"><option>bar</option><option>baz</option></select>',
        initialData: { foo: 'baz' },
    })

    await wait(() => {
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })
})

test('select element options are automatically selected by value attribute', async () => {
    testHarness.mount({
        dom: '<select wire:model="foo"><option value="bar">ignore</option><option value="baz">ignore</option></select>',
        initialData: { foo: 'baz' },
    })

    await wait(() => {
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })
})

test('select element options with numeric values work', async () => {
    testHarness.mount({
        dom: '<select wire:model="foo"><option value="1">ignore</option><option value="2">ignore</option></select>',
        initialData: { foo: 2 },
    })

    await wait(() => {
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })
})

test('multiple select element options are automatically selected', async () => {
    testHarness.mount({
        dom: '<select wire:model="foo" multiple><option>bar</option><option>baz</option></select>',
        initialData: { foo: 'baz' },
    })

    await wait(() => {
        expect(document.querySelectorAll('option')[0].selected).toBeFalsy()
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })

    testHarness.mount({
        dom: '<select wire:model="foo" multiple><option>bar</option><option>baz</option></select>',
        initialData: { foo: ['bar', 'baz'] },
    })

    await wait(() => {
        expect(document.querySelectorAll('option')[0].selected).toBeTruthy()
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })
})
