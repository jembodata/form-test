<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LHK Digital</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @filamentStyles
</head>

{{-- <body class="bg-white text-gray-900 font-sans">
    <header class="fixed top-0 left-0 w-full bg-white shadow-sm z-30">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="text-xl font-medium text-gray-800">
                LHK Digital
            </div>
            <nav class="space-x-4">
                <a href="/laporan/create" class="text-gray-600 hover:text-blue-600 transition">Buat Laporan</a>
                <a href="/admin" class="text-gray-600 hover:text-blue-600 transition">Admin</a>
            </nav>
        </div>
    </header>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('main-content').classList.add('visible');
        });
    </script>
</body> --}}

<body class="min-h-screen antialiased text-gray-900 relative overflow-x-hidden">
    {{-- <x-bg /> --}}
    <!-- Topbar -->
    <div class="relative z-10">
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-gray-200">
            <div class="mx-auto max-w-[1024px] px-3 sm:px-4 h-14 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-7 w-7 rounded-md bg-gray-900"></div>
                    <span class="font-semibold tracking-tight">LHK Digital</span>
                </div>
                {{-- <div class="text-sm text-gray-500 hidden sm:block">v1.0</div> --}}
                <nav class="space-x-4">
                    <a href="/laporan/create" class="text-gray-600 hover:text-blue-600 transition">Buat Laporan</a>
                    <a href="/admin" class="text-gray-600 hover:text-blue-600 transition">Admin</a>
                </nav>
            </div>
        </header>

        <!-- Main -->
        <main class="py-1">
            <div class="mx-auto w-full max-w-[1024px] px-3 sm:px-4 space-y-4">
                <!-- Page heading / actions -->

                <!-- The component -->
                <livewire:report-stats />
                <livewire:table-stats />
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6">
            <div class="mx-auto max-w-[1024px] px-3 sm:px-4 text-center text-xs text-white-500">
                &copy; {{ date('Y') }} • Made with ❤️ by Aditiya (IT)
            </div>
        </footer>
    </div>
    {{-- @livewireScripts --}}
</body>

</html>
