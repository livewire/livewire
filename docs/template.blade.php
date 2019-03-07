<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Livewire Docs</title>
    @gitdown
    <style>
        .markdown-body {
            box-sizing: border-box;
            min-width: 200px;
            max-width: 750px;
            padding: 25px;
        }

        @media (max-width: 767px) {
            .markdown-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div style="display: flex">
        <div class="markdown-body">
            {!! GitDown::parseAndCache($links->map(function ($linkTitle, $linkPath) use ($title) {
                $bold = $linkTitle == $title ? '**' : '';

                return sprintf('* %s[%s](%s)%s', $bold, $linkTitle, $linkPath, $bold);
            })->implode("\n")) !!}
        </div>

        <div class="markdown-body main-body">
            {!! $content !!}
        </div>
    </div>
</body>
</html>
