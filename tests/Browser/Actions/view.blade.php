<div>
    <button type="button" wire:click="setOutputToFoo" dusk="foo">Foo</button>
    <button type="button" wire:click="setOutputTo('bar', 'bell')" dusk="bar">Bar</button>
    <button type="button" wire:click="setOutputTo('a', &quot;b&quot; , 'c','d' ,'e', ''.concat('f'))" dusk="ball">Ball</button>
    <button type="button" wire:click="setOutputToFoo()" dusk="bowl">Bowl</button>
    <button type="button" wire:click="setOutputTo('baz')" dusk="baz.outer"><button type="button" wire:click="$refresh" dusk="baz.inner">Inner</button> Outer</button>
    <input type="text" wire:blur="appendToOutput('bop')" dusk="bop.input"><button type="button" wire:mousedown="appendToOutput('bop')" dusk="bop.button">Blur &</button>
    <input type="text" wire:keydown="appendToOutput('bob')" wire:keydown.enter="appendToOutput('bob')" dusk="bob">
    <input type="text" wire:keydown.enter="setOutputTo('lob')" dusk="lob">
    <input type="text" wire:keydown.shift.enter="setOutputTo('law')" dusk="law">
    <input type="text" wire:keydown.space="setOutputTo('spa')" dusk="spa">
    <form wire:submit.prevent="pause">
        <div wire:ignore>
            <input type="text" dusk="blog.input.ignored">
        </div>

        <input type="text" dusk="blog.input">
        <button type="submit" dusk="blog.button">Submit</button>
    </form>
    <form wire:submit.prevent="throwError">
        <button type="submit" dusk="boo.button">Submit</button>
    </form>
    <input wire:keydown.debounce.75ms="setOutputTo('bap')" dusk="bap"></button>
    <span dusk="output">{{ $output }}</span>

    <button type="button" wire:click="setShowButtonsWithClick" dusk="show.button.actions">Toggle Buttons with Actions</button>

    @if ($showButtonsWithClick)
        <div dusk="button.with-actions">
            <table>
                <tr>
                    <td>
                        <button type="button" wire:click="setOutputTo('button with wire:clicked got triggered')" dusk="button.with-click">Button - with wire:click</button>
                        <button id="btnWithAction" type="button" wire:click="setOutputTo('button with ID and wire:clicked got triggered')" dusk="button.with-id-and-click">Button with ID - with wire:click</button>
                    </td>
                </tr>
            </table>
        </div>
    @else
        <div>
            <table>
                <tr>
                    <td>
                        <button type="button">Button - no action</button>
                        <button id="btnNoAction" type="button">Button with ID - no action</button>
                    </td>
                </tr>
            </table>
        </div>
    @endif
</div>
