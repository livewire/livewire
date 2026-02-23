import { describe, expect, it } from 'vitest'
import { handleFileUpload } from './supportFileUploads'

function createComponentStub() {
    return {
        id: 'abc123',
        $wire: {
            $on: () => {},
            $watch: () => {},
            call: () => {},
        },
    }
}

describe('supportFileUploads cleanup', () => {
    it('removes the cancel listener when cleanup runs', () => {
        let component = createComponentStub()
        let input = document.createElement('input')
        let disposer = () => {}

        handleFileUpload(input, 'photo', component, (cleanup) => {
            disposer = cleanup
        })

        input.value = 'before-cancel'
        input.dispatchEvent(new CustomEvent('livewire-upload-cancel'))
        expect(input.value).toBe('')

        input.value = 'after-cleanup'
        disposer()
        input.dispatchEvent(new CustomEvent('livewire-upload-cancel'))

        expect(input.value).toBe('after-cleanup')
    })
})
