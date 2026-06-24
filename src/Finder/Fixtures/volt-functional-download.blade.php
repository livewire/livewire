<?php

$download = function () {
    $file = new \SplFileInfo('export.csv');

    return response()->download($file->getPathname());
};

?>

<div class="download">
    <button wire:click="download">Download</button>
</div>
