{{-- <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />

        <meta name="application-name" content="{{ config('app.name') }}" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>{{ config('app.name') }}</title>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @filamentStyles
        @vite('resources/css/app.css')
    </head>

    <body class="antialiased">
        {{ $slot }}

        @livewire('notifications')

        @filamentScripts
        @vite('resources/js/app.js')
    </body>
</html> --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="application-name" content="{{ config('app.name') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name') }}</title>

    <style>
        /* Fade-in effect */
        .fade-in {
            opacity: 0;
            transform: translateY(16px);
            transition: opacity 0.6s cubic-bezier(.4, 0, .2, 1), transform 0.6s cubic-bezier(.4, 0, .2, 1);
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    @vite('resources/css/app.css')
</head>

<body class="antialiased">
    <div id="page-fade" class="fade-in">
        {{ $slot }}
    </div>

    @livewire('notifications')
    @filamentScripts
    @vite('resources/js/app.js')

    <div class="border-gray-200 py-4 text-center text-gray-500 text-sm">
        Made with ❤️ by Aditiya (IT)
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('page-fade').classList.add('visible');
        });
        window.onload = function() {
            window.scrollTo(0, 0);
        };
    </script>
</body>

</html>
