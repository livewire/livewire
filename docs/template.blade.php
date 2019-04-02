<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Livewire Docs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css" rel="stylesheet">

    @gitdown
    <link href="/assets/template.css" rel="stylesheet">
</head>
<body class="font-source-sans font-normal text-black leading-normal">
    {{-- Navbar --}}
    <div class="bg-teal-dark flex items-center justify-between">
        <div style="min-width: 300px;" class="relative mr-20">
            <div style="width: 300px;position: absolute;left: 25px;margin-top: -25px;">
                {!! file_get_contents(public_path('assets/logo.svg')) !!}
            </div>
        </div>
        <div class="w-full flex items-center justify-end">
            <div class="pl-6 py-4 w-2/3 mr-8">
                <input type="text" class="outline-none px-3 py-2 rounded text-black text-xl w-full" placeholder="Search">
            </div>
            <div><a class="block flex text-white items-center hover:text-purple-lighter mr-6" href="https://github.com/laravel-livewire/livewire">
                <svg class="fill-current w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>GitHub</title><path d="M10 0a10 10 0 0 0-3.16 19.49c.5.1.68-.22.68-.48l-.01-1.7c-2.78.6-3.37-1.34-3.37-1.34-.46-1.16-1.11-1.47-1.11-1.47-.9-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.9 1.52 2.34 1.08 2.91.83.1-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.94 0-1.1.39-1.99 1.03-2.69a3.6 3.6 0 0 1 .1-2.64s.84-.27 2.75 1.02a9.58 9.58 0 0 1 5 0c1.91-1.3 2.75-1.02 2.75-1.02.55 1.37.2 2.4.1 2.64.64.7 1.03 1.6 1.03 2.69 0 3.84-2.34 4.68-4.57 4.93.36.31.68.92.68 1.85l-.01 2.75c0 .26.18.58.69.48A10 10 0 0 0 10 0"></path></svg>
            </a></div>
        </div>
    </div>

    <div class="flex pt-12">
        {{-- Sidebar --}}
        <div class="p-8 pt-4" style="border-color:#444">
            <ul class="list-reset">
                @foreach ($links as $path => $linkTitle)
                    <li class="mb-2"><a class="no-underline text-black text-sm" href="{{ $path }}">
                        {!! $linkTitle === $title ? '<strong>'.$linkTitle.'</strong>' : $linkTitle !!}
                    </a></li>
                @endforeach
            </ul>
        </div>

        {{-- Content --}}
        <div class="markdown-body main-body p-8 pt-4 pl-16">
            {!! GitDown\Facades\GitDown::parseAndCache($content) !!}
        </div>
    </div>
</body>
</html>
