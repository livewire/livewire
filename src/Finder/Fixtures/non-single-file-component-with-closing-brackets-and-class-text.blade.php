<?php

$download = function () {
    $file = new \SplFileInfo('#[value] class');
    $options = ['] class'];

    return response()->download($file->getPathname(), options: $options);
};

?>

<div class="download">
    <button wire:click="download">Download</button>
</div>
