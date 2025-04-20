<x-mail::message>
# One-Time Password (OTP)

Halo!

Kode OTP anda adalah:

<x-mail::panel>
    {{$otp}}
</x-mail::panel>

Kode OTP ini akan kedaluwarsa dalam 5 menit.

Terimakasih,<br>
{{ config('app.name') }}
</x-mail::message>
