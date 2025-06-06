<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <title>Pembayaran Gagal - SleepCare</title>
</head>
<body class="bg-white text-gray-900">
<header class="flex flex-wrap justify-between items-center p-6 border-b border-gray-200">
    <a class="text-xl font-bold text-[#155DFCFF]" href="/">SleepCare</a>
</header>

<section class="px-6 py-24 bg-[#51A2FFFF] text-center text-white">
    <h1 class="text-4xl font-bold mb-4">Pembayaran Gagal ❌</h1>
    <p class="text-lg mb-6">Terjadi kesalahan dalam proses pembayaran. Silakan coba lagi.</p>
    <p class="text-sm mt-4">ID Pesanan: {{ request('order_id') }}</p>
</section>

<footer class="py-10 px-6 bg-gray-50">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 text-sm text-gray-700 gap-6">
        <div>
            <h2 class="font-bold text-base mb-2 text-[#155DFCFF]">SleepCare</h2>
        </div>
        <div class="space-y-4">
            <div>
                <h3 class="font-bold mb-2">Download App di</h3>
                <a href="#" class="inline-block px-4 py-2 border border-[#155DFCFF] text-[#155DFCFF] rounded hover:bg-[#155DFCFF] hover:text-white transition">
                    Unduh Sekarang
                </a>
            </div>
            <div>
                <h3 class="font-bold mb-2">Apakah Kamu Psikolog?</h3>
                <a href="{{ route('register') }}" class="inline-block px-4 py-2 border border-[#155DFCFF] text-[#155DFCFF] rounded hover:bg-[#155DFCFF] hover:text-white transition">
                    Daftar Sebagai Psikolog
                </a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
