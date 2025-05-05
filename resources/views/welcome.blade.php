<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="bg-white text-gray-900">
<header class="flex flex-wrap justify-between items-center p-6 border-b border-gray-200">
    <a class="text-xl font-bold text-[#D91E36]" href="/">SleepCare</a>
    @auth
        <href class="px-4 py-2 mt-4 md:mt-0 text-sm border border-[#D91E36] text-[#D91E36] rounded hover:bg-[#D91E36] hover:text-white transition" href="{{route('dashboard')}}">
            Dashboard
        </href>
    @else
        <a class="px-4 py-2 mt-4 md:mt-0 text-sm border border-[#D91E36] text-[#D91E36] rounded hover:bg-[#D91E36] hover:text-white transition" href="{{route('login')}}">
            Login
        </a>
    @endauth
</header>

<section class="px-6 py-16 bg-[#FDEAEA]">
    <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-8 ">
        Solusi Terapi<br />
        Insomnia Terlengkap
    </h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        <div class="border border-gray-200 rounded p-4 text-center bg-white">
            <div>
                <img src="{{ asset('storage/img/welcome/psychologist.svg') }}" alt="" class="w-50 mx-auto" loading="lazy">
            </div>
            <p class="text-sm font-medium mt-2">Terapi ACT Dengan Bantuan Psikolog</p>
        </div>
        <div class="border border-gray-200 rounded p-4 text-center bg-white">
            <div>
                <img src="{{ asset('storage/img/welcome/phone.svg') }}" alt="" class="w-50 mx-auto" loading="lazy">
            </div>
            <p class="text-sm font-medium mt-2">Pencatatan Data Terapi</p>
        </div>
        <div class="border border-gray-200 rounded p-4 text-center bg-white">
            <div>
                <img src="{{ asset('storage/img/welcome/chatbot.svg') }}" alt="" class="w-50 mx-auto" loading="lazy">
            </div>
            <p class="text-sm font-medium mt-2">Chatbot Informasi Tentang Insomnia & Terapi ACT</p>
        </div>
    </div>

</section>

<section class="px-6 py-14 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
    <div>
        <img src="{{ asset('storage/img/welcome/medical.svg') }}" alt="" class="w-70 mx-auto" loading="lazy">
    </div>
    <div>
        <h2 class="text-2xl md:text-3xl font-bold mb-4">
            Aplikasi Terapi Insomnia Terlengkap di Indonesia
        </h2>
        <button class="px-5 py-2 bg-[#D91E36] text-white text-sm rounded hover:bg-[#bb192e] transition">
            Unduh Sekarang
        </button>
    </div>
</section>

<section class="px-6 py-12 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 text-center">
    <div>
        <div class="mb-2 text-2xl">✅</div>
        <h3 class="font-semibold mb-1">Aplikasi Terapi Terlengkap</h3>
        <p class="text-sm text-gray-600">Menggunakan metode ACT dengan bantuan psikolog dan pencatatan data terapi.</p>
    </div>
    <div>
        <div class="mb-2 text-2xl">⚡</div>
        <h3 class="font-semibold mb-1">Mudah, Aman & Praktis</h3>
        <p class="text-sm text-gray-600">Sesi terapi berdurasi 1 jam setiap minggu selama 6 minggu.</p>
    </div>
    <div>
        <div class="mb-2 text-2xl">🌍</div>
        <h3 class="font-semibold mb-1">Kapan dan Dimana Saja</h3>
        <p class="text-sm text-gray-600">Nikmati terapi online yang dapat diakses dari mana saja.</p>
    </div>
</section>

<section class="bg-[#FDEAEA] py-5 px-3">
    <div class="text-center mb-4 font-medium">
        Mari Bergabung Sebagai Mitra Psikolog di SleepCare!
    </div>
    <div class="text-center mb-4">
        <a class="px-4 py-2 text-sm border border-[#D91E36] text-[#D91E36] rounded hover:bg-[#D91E36] hover:text-white transition" href="{{route('register')}}">
            Daftar Sebagai Psikolog
        </a>
    </div>
</section>

<footer class="py-10 px-6 bg-gray-50">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 text-sm text-gray-700 gap-6">
        <!-- Brand Section -->
        <div>
            <h2 class="font-bold text-base mb-2 text-[#D91E36]">SleepCare</h2>
        </div>

        <!-- Download and Psychologist Registration Section -->
        <div class="space-y-4">
            <div>
                <h3 class="font-bold mb-2">Download App di</h3>
                <a href="#" class="inline-block px-4 py-2 border border-[#D91E36] text-[#D91E36] rounded hover:bg-[#D91E36] hover:text-white transition">
                    Unduh Sekarang
                </a>
            </div>
            <div>
                <h3 class="font-bold mb-2">Apakah Kamu Psikolog?</h3>
                <a href="{{route('register')}}" class="inline-block px-4 py-2 border border-[#D91E36] text-[#D91E36] rounded hover:bg-[#D91E36] hover:text-white transition">
                    Daftar Sebagai Psikolog
                </a>
            </div>
        </div>
    </div>
</footer>


</body>
</html>

