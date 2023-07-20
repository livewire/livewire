<div>
    <h1 dusk="single.output">{{ $single }}</h1>
    <select wire:model.live="single" dusk="single.input">
        <option>foo</option>
        <option>bar</option>
        <option>baz</option>
    </select>

    <h1 dusk="single-value.output">{{ $singleValue }}</h1>
    <select wire:model.live="singleValue" dusk="single-value.input">
        <option value="poo">foo</option>
        <option value="par">bar</option>
        <option value="paz">baz</option>
    </select>

    <h1 dusk="single-number.output">{{ $singleNumber }}</h1>
    <select wire:model.live="singleNumber" dusk="single-number.input">
        <option value="2">foo</option>
        <option value="3">bar</option>
        <option value="4">baz</option>
    </select>

    <h1 dusk="placeholder.output">{{ $placeholder }}</h1>
    <select wire:model.live="placeholder" dusk="placeholder.input">
        <option value="" disabled>Placeholder</option>
        <option>foo</option>
        <option>bar</option>
        <option>baz</option>
    </select>

    <h1 dusk="multiple.output">@json($multiple)</h1>
    <select wire:model.live.debounce="multiple" multiple dusk="multiple.input">
        <option>foo</option>
        <option>bar</option>
        <option>baz</option>
    </select>
</div>
