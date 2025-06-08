<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function download()
    {
        $path = storage_path('app/public/apk/sleep-care.apk');

        if (! file_exists($path)) {
            abort(404, 'Aplikasi sleepcare tidak ditemukan.');
        }

        return response()->download($path, 'sleep-care.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
