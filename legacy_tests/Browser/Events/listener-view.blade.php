<div>
    <span dusk="messageDisplay">{{ $message }}</span>

    <button id="emitEvent" wire:click="$emit('echo:message', { message: 'Test Message' })" dusk="emit.event">Emit Event</button>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cleanup = $wire.on('echo:message', (event) => {
                console.log('Event received:', event.message);
                window.eventReceived = event.message;
                $wire.set('message', event.message);
            });

            document.getElementById('emitEvent').addEventListener('click', () => {
                cleanup();
            });
        });
    </script>
</div>
