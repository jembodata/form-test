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
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <!-- Animasi background custom CSS -->
    <style>
        .area {
            background: #4e54c8;
            background: -webkit-linear-gradient(to left, #8f94fb, #4e54c8);
            width: 100%;
            height: 100vh;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
            overflow: hidden;
        }

        .circles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .circles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            animation: animate 25s linear infinite;
            bottom: -150px;
        }

        .circles li:nth-child(1) {
            left: 25%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }

        .circles li:nth-child(2) {
            left: 10%;
            width: 20px;
            height: 20px;
            animation-delay: 2s;
            animation-duration: 12s;
        }

        .circles li:nth-child(3) {
            left: 70%;
            width: 20px;
            height: 20px;
            animation-delay: 4s;
        }

        .circles li:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 18s;
        }

        .circles li:nth-child(5) {
            left: 65%;
            width: 20px;
            height: 20px;
            animation-delay: 0s;
        }

        .circles li:nth-child(6) {
            left: 75%;
            width: 110px;
            height: 110px;
            animation-delay: 3s;
        }

        .circles li:nth-child(7) {
            left: 35%;
            width: 150px;
            height: 150px;
            animation-delay: 7s;
        }

        .circles li:nth-child(8) {
            left: 50%;
            width: 25px;
            height: 25px;
            animation-delay: 15s;
            animation-duration: 45s;
        }

        .circles li:nth-child(9) {
            left: 20%;
            width: 15px;
            height: 15px;
            animation-delay: 2s;
            animation-duration: 35s;
        }

        .circles li:nth-child(10) {
            left: 85%;
            width: 150px;
            height: 150px;
            animation-delay: 0s;
            animation-duration: 11s;
        }

        @keyframes animate {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
                border-radius: 0;
            }

            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
                border-radius: 50%;
            }
        }

        /* Fade-in animasi untuk konten utama */
        .fade-in {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.7s cubic-bezier(.4, 0, .2, 1), transform 0.7s cubic-bezier(.4, 0, .2, 1);
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body class="bg-white text-gray-900 font-sans">

    <!-- Header / Navigation -->
    <header class="fixed top-0 left-0 w-full bg-white shadow-sm z-10">
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

    <!-- Main Section dengan background animasi -->
    <section class="relative flex items-center justify-center h-screen">
        <!-- Animasi Background -->
        <div class="area">
            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </div>

        <!-- Konten utama di atas animasi -->
        <div id="main-content" class="text-center px-6 md:px-8 z-20 relative fade-in">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="p-4">
                    <div>
                        @livewire(\App\Filament\Widgets\LatestLaporans::class)
                    </div>
                </div>

                <!-- Right Column (Sidebar or additional content) -->
                <div class="p-4">
                    <div class="pb-4">
                        @livewire(\App\Filament\Widgets\StatsOverview::class)
                    </div>
                    <div>
                        {{-- b --}}
                    </div>

                </div>
            </div>
        </div>
    </section>
    <div class="border-gray-200 py-4 text-center text-gray-500 text-sm">
        Made with ❤️ by Aditiya (IT)
    </div>

    <script>
        // Saat halaman sudah siap, jalankan animasi fade-in
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('main-content').classList.add('visible');
        });
    </script>
</body>

</html>
