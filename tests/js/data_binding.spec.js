import { fireEvent, wait, waitForDomChange } from 'dom-testing-library'
import { mount, mountWithData, mountAndReturn, mountAndReturnWithData } from './utils'

test('properties sync on input change', async () => {
    var payload
    mount('<input wire:model="foo">', i => payload = i)

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual('bar')
    })
})

test('nested properties sync on input change', async () => {
    var payload
    mountWithData(
        '<input wire:model="foo.one.two">',
        { foo: [] },
        i => payload = i
    )

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo.one.two')
        expect(payload.actionQueue[0].payload.value).toEqual('bar')
    })
})

test('properties are lazy synced when action is fired', async () => {
    var payload
    mount('<input wire:model.lazy="foo"><button wire:click="onClick"></button>', i => payload = i)

    fireEvent.change(document.querySelector('input'), { target: { value: 'bar' }})

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual('bar')
    })
})

test('properties are passively synced', async () => {
    let payload
    let requestCount = 0
    mount('<input id="passive" wire:model.passive="passive" /><button wire:click="onClick">', i => {
        payload = i
        requestCount++
    })

    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData' }})

    expect(requestCount).toEqual(0)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(requestCount).toEqual(1)
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('passive')
        expect(payload.actionQueue[0].payload.value).toEqual('passiveData')

        expect(payload.actionQueue[1].type).toEqual('callMethod')
        expect(payload.actionQueue[1].payload.method).toEqual('onClick')
    })
})

test('passive models only appear once in the action queue', async () => {
    let payload
    mount('<input id="passive" wire:model.passive="passive" /><button wire:click="onClick">', i => {
        payload = i
    })

    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData' }})
    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData2' }})

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue.length).toEqual(2)

        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('passive')
        expect(payload.actionQueue[0].payload.value).toEqual('passiveData2')

        expect(payload.actionQueue[1].type).toEqual('callMethod')
        expect(payload.actionQueue[1].payload.method).toEqual('onClick')
    })
})

test('passive models always have default debounce', async () => {
    let payload
    mount('<input id="passive" wire:model.passive.debounce.100s="passive" /><button wire:click="onClick">', i => {
        payload = i
    })

    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData' }})
    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData2' }})

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue.length).toEqual(2)

        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('passive')
        expect(payload.actionQueue[0].payload.value).toEqual('passiveData2')

        expect(payload.actionQueue[1].type).toEqual('callMethod')
        expect(payload.actionQueue[1].payload.method).toEqual('onClick')
    })
})

test('passive models appear before active', async () => {
    let payload
    mount('<input id="passive" wire:model.passive="passive" /><input id="passive2" wire:model.passive="passive2" /><input id="active" wire:model="active" />', i => {
        payload = i
    })

    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData' }})
    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData2' }})

    fireEvent.input(document.querySelector('#passive2'), { target: { value: 'passive2Data' }})
    fireEvent.input(document.querySelector('#passive2'), { target: { value: 'passive2Data2' }})

    fireEvent.input(document.querySelector('#active'), { target: { value: 'activeData' }})

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('passive')
        expect(payload.actionQueue[0].payload.value).toEqual('passiveData2')

        expect(payload.actionQueue[1].type).toEqual('syncInput')
        expect(payload.actionQueue[1].payload.name).toEqual('passive2')
        expect(payload.actionQueue[1].payload.value).toEqual('passive2Data2')

        expect(payload.actionQueue[2].type).toEqual('syncInput')
        expect(payload.actionQueue[2].payload.name).toEqual('active')
        expect(payload.actionQueue[2].payload.value).toEqual('activeData')
    })
})

test('passive models work on form submits', async () => {
    let payload
    mount('<form wire:submit.prevent="submit"><input id="passive" wire:model.passive="passive" /></form>', i => {
        payload = i
    })

    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData' }})
    fireEvent.input(document.querySelector('#passive'), { target: { value: 'passiveData2' }})

    fireEvent.submit(document.querySelector('form'))

    await wait(() => {
        expect(payload.actionQueue.length).toEqual(2)

        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('passive')
        expect(payload.actionQueue[0].payload.value).toEqual('passiveData2')

        expect(payload.actionQueue[1].type).toEqual('callMethod')
        expect(payload.actionQueue[1].payload.method).toEqual('submit')
    })
})

test('textarea data binding with class change works as expected and doesn\'t wipe its value', async () => {
    mountAndReturn(
        '<textarea wire:model="foo" class="foo"></textarea>',
        '<textarea wire:model="foo" class="foo bar"></textarea>',
        []
    )

    fireEvent.input(document.querySelector('textarea'), { target: { value: 'bar' }})

    await wait(() => {
        expect(document.querySelector('textarea').value).toEqual('bar')
        expect(document.querySelector('textarea').classList.contains('bar')).toBeTruthy()
    })
})

test('input element value doesnt change unless property is marked as dirty', async () => {
    mountAndReturn(
        '<input wire:model="foo" value="">',
        '<input wire:model="foo" value="bar"><button>Im here to trigger dom change</button>',
        []
    )

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await waitForDomChange(document.body, () => {
        expect(document.querySelector('input').value).toEqual('baz')
    })

    mountAndReturn(
        '<input wire:model="foo" value="">',
        '<input wire:model="foo" value="bar"><button>Im here to trigger dom change</button>',
        ['foo']
    )

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await waitForDomChange(document.body, () => {
        expect(document.querySelector('input').value).toEqual('bar')
    })
})

test('input element value doesnt change, but other attributes do when not marked as dirty', async () => {
    mountAndReturn(
        '<input wire:model="foo" class="foo" value="">',
        '<input wire:model="foo" class="foo bar" value="bar">',
        []
    )

    document.querySelector('input').focus()
    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await wait(() => {
        expect(document.querySelector('input').value).toEqual('baz')
        expect(document.querySelector('input').classList.contains('bar')).toBeTruthy()
    })
})

test('input element value attribute is automatically updated if present in returned dom', async () => {
    mountAndReturnWithData(
        '<input wire:model="foo"><button wire:click="onClick"></button>',
        '<input wire:model="foo"><button wire:click="onClick"></button>',
        { foo: 'bar' }, ['foo']
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('input').value).toBe('bar')
    })
})

test('input element value is automatically updated', async () => {
    mountWithData(
        '<input wire:model="foo">',
        { foo: 'bar' }
    )

    await wait(() => {
        expect(document.querySelector('input').value).toBe('bar')
    })
})

test('textarea element value is automatically updated', async () => {
    mountWithData(
        '<textarea wire:model="foo"></textarea>',
        { foo: 'bar' }
    )

    await wait(() => {
        expect(document.querySelector('textarea').value).toBe('bar')
    })
})

test('checkbox element value attribute is automatically added if not present in the initial dom', async () => {
    mountWithData(
        '<input type="checkbox" wire:model="foo">',
        { foo: true }
    )

    await wait(() => {
        expect(document.querySelector('input').checked).toBeTruthy()
    })
})

test('checkboxes bound to empty array arent checked', async () => {
    mountWithData(
        `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        { foo: [] },
    )
    expect(document.querySelector('#a').checked).toBeFalsy()
})

test('checkboxes bound to an array containing value are checked', async () => {
    mountWithData(
        `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        { foo: ['a'] },
    )
    expect(document.querySelector('#a').checked).toBeTruthy()
})

test('checkboxes bound to an array containing a numeric value are checked', async () => {
    mountWithData(
        `<input id="a" type="checkbox" wire:model="foo" value="2">`,
        { foo: [2] },
    )
    expect(document.querySelector('#a').checked).toBeTruthy()
})

test('checkboxes bound to an array containing a different value are not', async () => {
    mountWithData(
        `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        { foo: ['b'] },
    )
    expect(document.querySelector('#a').checked).toBeFalsy()
})

test('checking a checkbox bound to an array will toggle its value inside the array', async () => {
    var payload
    mountWithData(
        `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        { foo: [] },
        i => payload = i
    )

    fireEvent.click(document.querySelector('#a'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual(['a'])
    })

    var payload
    mountWithData(
        `<input id="a" type="checkbox" wire:model="foo" value="a">`,
        { foo: ['a'] },
        i => payload = i
    )

    fireEvent.click(document.querySelector('#a'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual([])
    })
})

test('select element options are automatically selected', async () => {
    mountWithData(
        '<select wire:model="foo"><option>bar</option><option>baz</option></select>',
        { foo: 'baz' }
    )

    await wait(() => {
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })
})

test('select element options are automatically selected by value attribute', async () => {
    mountWithData(
        '<select wire:model="foo"><option value="bar">ignore</option><option value="baz">ignore</option></select>',
        { foo: 'baz' }
    )

    await wait(() => {
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })
})

test('select element options with numeric values work', async () => {
    mountWithData(
        '<select wire:model="foo"><option value="1">ignore</option><option value="2">ignore</option></select>',
        { foo: 2 }
    )

    await wait(() => {
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })
})

test('multiple select element options are automatically selected', async () => {
    mountWithData(
        '<select wire:model="foo" multiple><option>bar</option><option>baz</option></select>',
        { foo: 'baz' }
    )

    await wait(() => {
        expect(document.querySelectorAll('option')[0].selected).toBeFalsy()
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })

    mountWithData(
        '<select wire:model="foo" multiple><option>bar</option><option>baz</option></select>',
        { foo: ['bar', 'baz'] }
    )

    await wait(() => {
        expect(document.querySelectorAll('option')[0].selected).toBeTruthy()
        expect(document.querySelectorAll('option')[1].selected).toBeTruthy()
    })
})
