<div>
    <button wire:click="$emit('download')" dusk="emit-download">Emit Download</button>
    <button wire:click="download" dusk="download">Download</button>
    <button wire:click="downloadFromResponse" dusk="download-from-response">Download</button>
    <button wire:click="downloadQuotedContentDispositionFilename" dusk="download-quoted-disposition-filename">Download</button>
    <button wire:click="downloadQuotedContentDispositionFilenameFromResponse" dusk="download-from-response-quoted-disposition-filename">Download</button>
    <button wire:click="downloadWithContentTypeHeader('text/html')" dusk="download-with-content-type-header">Download</button>
    <button wire:click="downloadWithContentTypeHeader()" dusk="download-with-null-content-type-header">Download</button>
    <button wire:click="downloadAnUntitledFileWithContentTypeHeader" dusk="download-an-untitled-file-with-content-type-header">Download</button>
    <button wire:click="downloadAnUntitledFileWithContentTypeHeader('foo')" dusk="download-an-untitled-file-with-invalid-content-type-header">Download</button>
    <button wire:click="downloadFromResponseWithContentTypeHeader" dusk="download-from-response-with-content-type-header">Download</button>
    <input dusk="content-type" />
</div>
