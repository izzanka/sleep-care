<x-mail::message>
# Reset Password OTP

Halo!

Kode OTP anda untuk reset password adalah:

<x-mail::panel>
    {{$otp}}
</x-mail::panel>

Kode OTP reset password ini akan kedaluwarsa dalam 5 menit.

Terimakasih,<br>
{{ config('app.name') }}
</x-mail::message>
