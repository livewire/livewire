<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Livewire Docs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" rel="stylesheet">
    @gitdown
    <style>
        .bg-yellow {
            background-color: #FFE771;
        }

        body {
            margin: 0;
        }

        .markdown-body .highlight pre, .markdown-body pre {
            background-color: #fff9dd;
            border: 1px solid #222;
            border-radius: 9px;
            /* box-shadow: 1px 1px 0px 1px #FF603E, 2px 2px 0px 1px #FF603E, 3px 3px 0px 1px #FF603E, 4px 4px 0px 1px #FF603E, 5px 5px 0px 1px #FFA523, 6px 6px 0px 1px #FFA523, 7px 7px 0px 1px #FFA523, 8px 8px 0px 1px #FFA523, 9px 9px 0px 1px #9BEBB0, 10px 10px 0px 1px #9BEBB0, 11px 11px 0px 1px #9BEBB0, 12px 12px 0px 1px #9BEBB0, 13px 13px 0px 1px #7BC092, 14px 14px 0px 1px #7BC092, 15px 15px 0px 1px #7BC092, 16px 16px 0px 1px #7BC092; */
            box-shadow: 1px 1px 0px 1px #FF603E, 2px 2px 0px 1px #FF603E, 3px 3px 0px 1px #FF603E, 4px 4px 0px 1px #FF603E, 5px 5px 0px 1px #FFA523, 6px 6px 0px 1px #FFA523, 7px 7px 0px 1px #FFA523, 8px 8px 0px 1px #FFA523, 9px 9px 0px 1px #9BEBB0, 10px 10px 0px 1px #9BEBB0, 11px 11px 0px 1px #9BEBB0, 12px 12px 0px 1px #9BEBB0, 13px 13px 0px 1px #7BC092, 14px 14px 0px 1px #7BC092, 15px 15px 0px 1px #7BC092, 16px 16px 0px 1px #7BC092, 17px 17px 0px 1px #CF606A, 18px 18px 0px 1px #CF606A, 19px 19px 0px 1px #CF606A, 20px 20px 0px 1px #CF606A;
            margin-bottom: 32px;
        }

        .markdown-body h1, .markdown-body h2 {
            border-bottom: 1px solid #444;
        }

        .markdown-body code, .markdown-body tt {
            border: 1px solid #444;
            background-color: #fff9dd;
        }
        /* 1px 1px 0px 1px #FF603E, 2px 2px 0px 1px #FF603E, 3px 3px 0px 1px #FF603E, 4px 4px 0px 1px #FF603E, 5px 5px 0px 1px #FFA523, 6px 6px 0px 1px #FFA523, 7px 7px 0px 1px #FFA523, 8px 8px 0px 1px #FFA523, 9px 9px 0px 1px #9BEBB0, 10px 10px 0px 1px #9BEBB0, 11px 11px 0px 1px #9BEBB0, 12px 12px 0px 1px #9BEBB0, 13px 13px 0px 1px #7BC092, 14px 14px 0px 1px #7BC092, 15px 15px 0px 1px #7BC092, 16px 16px 0px 1px #7BC092, 17px 17px 0px 1px #CF606A, 18px 18px 0px 1px #CF606A, 19px 19px 0px 1px #CF606A, 20px 20px 0px 1px #CF606A */
        /* 1px 1px 0px 1px #FF603E, 2px 2px 0px 1px #FF603E, 3px 3px 0px 1px #FF603E, 4px 4px 0px 1px #FF603E, 5px 5px 0px 1px #9BEBB0, 6px 6px 0px 1px #9BEBB0, 7px 7px 0px 1px #9BEBB0, 8px 8px 0px 1px #9BEBB0, 9px 9px 0px 1px #CF606A, 10px 10px 0px 1px #CF606A, 11px 11px 0px 1px #CF606A, 12px 12px 0px 1px #CF606A */
        /* 1px 1px 0px 1px #FF603E, 2px 2px 0px 1px #FF603E, 3px 3px 0px 1px #FF603E, 4px 4px 0px 1px #FF603E, 5px 5px 0px 1px #FFA523, 6px 6px 0px 1px #FFA523, 7px 7px 0px 1px #FFA523, 8px 8px 0px 1px #FFA523, 9px 9px 0px 1px #9BEBB0, 10px 10px 0px 1px #9BEBB0, 11px 11px 0px 1px #9BEBB0, 12px 12px 0px 1px #9BEBB0 */
        /* 1px 1px 0px 1px #FF603E, 2px 2px 0px 1px #FF603E, 3px 3px 0px 1px #FF603E, 4px 4px 0px 1px #FF603E, 5px 5px 0px 1px #FFA523, 6px 6px 0px 1px #FFA523, 7px 7px 0px 1px #FFA523, 8px 8px 0px 1px #FFA523, 9px 9px 0px 1px #9BEBB0, 10px 10px 0px 1px #9BEBB0, 11px 11px 0px 1px #9BEBB0, 12px 12px 0px 1px #9BEBB0, 13px 13px 0px 1px #7BC092, 14px 14px 0px 1px #7BC092, 15px 15px 0px 1px #7BC092, 16px 16px 0px 1px #7BC092 */

        .markdown-body {
            box-sizing: border-box;
            min-width: 200px;
            max-width: 750px;
        }
    </style>
</head>
<body class="font-source-sans font-normal text-black leading-normal bg-yellow-lightest">
    {{-- <div style="display: flex"> --}}
        {{-- <div style="height: 10px; background-color: #FF603E; width: 100%"></div>
        <div style="height: 10px; background-color: #FFA523; width: 100%"></div>
        <div style="height: 10px; background-color: #9BEBB0; width: 100%"></div>
        <div style="height: 10px; background-color: #7BC092; width: 100%"></div>
        <div style="height: 10px; background-color: #CF606A; width: 100%"></div> --}}
    {{-- </div> --}}
    {{-- <div style="height: 4px; background-color: #000; width: 100%"></div> --}}

    <div class="bg-yellow border-b border-grey-darkest flex items-center justify-between">
        <div class="w-full">
            <div class="pl-6 py-4">
                <input type="text" class="w-2/3 bg-yellow-lightest border border-black outline-none px-3 py-2 rounded text-xl" placeholder="Search">
            </div>
        </div>
        <div class="w-full">
            <div style="width: 600px;margin-top: -125px;">
                {!! file_get_contents($svgPath) !!}
            </div>
        </div>
    </div>

    <div class="flex pt-4">
        <div class="p-8">
            <ul class="list-reset">
                @foreach ($links as $path => $linkTitle)
                    <li class="mb-2"><a class="no-underline text-grey-darkest" href="{{ $path }}">
                        {!! $linkTitle === $title ? '<strong>'.$linkTitle.'</strong>' : $linkTitle !!}
                    </a></li>
                @endforeach
            </ul>
        </div>

        <div class="markdown-body main-body p-8 pl-16">
            {!! $content !!}
        </div>
    </div>
</body>
</html>
