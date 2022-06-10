<div>
    <h1></h1>

    <div id="target"></div>

    <button id="click_me" wire:click="clickMe">Click me</button>

    <script>
        const me = document.currentScript
        document.addEventListener('livewire:load', function () {
            const target = document.querySelector('#target')
            target.attachShadow({ mode: 'open' })
                .appendChild(document.querySelector('#click_me'))

            // Reattach wires to this component (without this the test will fail because click_me cant be clicked)
            const component = target.closest('[wire\\3A id]')
            component.__livewire.tearDown()
            component.__livewire.initialize()

            document.querySelector('h1').innerText = 'Step 1 Active';
        });
    </script>

    @if($success)
        <h2>Test succeeded</h2>
    @endif
</div>
