<div>
    <form wire:submit="dynamicValidation">
        <label for="dynamicForm.name">Name</label>
        <input wire:model="dynamicForm.name" dusk="dynamicForm.name" id="dynamicForm.name" type="text"><span dusk="output.dynamic.form.name">@error('dynamicForm.name') {{ $message }} @enderror</span>

        <label for="dynamicForm.body">Body</label>
        <textarea wire:model="dynamicForm.body" id="dynamicForm.body" type="text"></textarea><span dusk="output.dynamic.form.body">@error('dynamicForm.body') {{ $message }} @enderror</span>
        <button type="submit" dusk="dynamicForm">Submit</button>
    </form>

    <form wire:submit="defaultValidation">
        <label for="defaultForm.name">Name</label>
        <input wire:model="defaultForm.name" dusk="defaultForm.name" id="defaultForm.name" type="text"><span dusk="output.default.form.name">@error('defaultForm.name') {{ $message }} @enderror</span>

        <label for="defaultForm.body">Body</label>
        <textarea wire:model="defaultForm.body" id="defaultForm.body" type="text"></textarea><span dusk="output.default.form.body">@error('defaultForm.body') {{ $message }} @enderror</span>
        <button type="submit" dusk="defaultForm">Submit</button>
    </form>
</div>