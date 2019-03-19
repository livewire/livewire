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
            margin-bottom: 55px;
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
<body class="antialiased font-source-sans font-normal text-black leading-normal bg-yellow-lightest">
    {{-- <div style="display: flex"> --}}
        {{-- <div style="height: 10px; background-color: #FF603E; width: 100%"></div>
        <div style="height: 10px; background-color: #FFA523; width: 100%"></div>
        <div style="height: 10px; background-color: #9BEBB0; width: 100%"></div>
        <div style="height: 10px; background-color: #7BC092; width: 100%"></div>
        <div style="height: 10px; background-color: #CF606A; width: 100%"></div> --}}
    {{-- </div> --}}
    {{-- <div style="height: 4px; background-color: #000; width: 100%"></div> --}}

    <div class="bg-yellow border-b border-yellow-dark flex items-center justify-between">
        <div style="min-width: 300px;" class="relative mr-20">
            <div style="width: 300px;position: absolute;left: 25px;margin-top: -25px;">
                {!! file_get_contents($svgPath) !!}
            </div>
        </div>
        <div class="w-full flex items-center justify-end">
            <div class="pl-6 py-4 w-2/3 mr-8">
                <input type="text" class="bg-yellow-lightest outline-none px-3 py-2 rounded text-black text-xl w-full" placeholder="Search">
            </div>
            <div><a class="block flex text-black items-center hover:text-grey-darkest mr-6" href="https://github.com/laravel-livewire/livewire">
                <svg class="fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>GitHub</title><path d="M10 0a10 10 0 0 0-3.16 19.49c.5.1.68-.22.68-.48l-.01-1.7c-2.78.6-3.37-1.34-3.37-1.34-.46-1.16-1.11-1.47-1.11-1.47-.9-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.9 1.52 2.34 1.08 2.91.83.1-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.94 0-1.1.39-1.99 1.03-2.69a3.6 3.6 0 0 1 .1-2.64s.84-.27 2.75 1.02a9.58 9.58 0 0 1 5 0c1.91-1.3 2.75-1.02 2.75-1.02.55 1.37.2 2.4.1 2.64.64.7 1.03 1.6 1.03 2.69 0 3.84-2.34 4.68-4.57 4.93.36.31.68.92.68 1.85l-.01 2.75c0 .26.18.58.69.48A10 10 0 0 0 10 0"></path></svg>
            </a></div>
        </div>
    </div>

    <div class="flex pt-12">
        <div class="p-8 pt-4 border-r" style="border-color:#444">
            <ul class="list-reset">
                @foreach ($links as $path => $linkTitle)
                    <li class="mb-2"><a class="no-underline text-black" href="{{ $path }}">
                        {!! $linkTitle === $title ? '<strong>'.$linkTitle.'</strong>' : $linkTitle !!}
                    </a></li>
                @endforeach
            </ul>
        </div>

        <div class="markdown-body main-body p-8 pt-4 pl-16">
            {!! $content !!}
        </div>
    </div>
</body>
</html>
