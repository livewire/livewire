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

    <button type="button" wire:click="showButtonGroup()" dusk="show.button.group">Show Button Group</button>

    @if ($showButtonGroup)
        <div dusk="button.group">
            <table>
                <tr>
                    <td>
                        <button type="button" wire:click="setOutputTo('button1 clicked')" dusk="button.group.1">Button 1</button>
                        <button type="button" wire:click="setOutputTo('button2 clicked')" dusk="button.group.2">Button 2</button>
                    </td>
                </tr>
            </table>
        </div>
    @endif
</div>
