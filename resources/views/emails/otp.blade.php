<x-mail::message>
# One-Time Password (OTP)

Hello!

Your OTP code is:

<x-mail::panel>
    {{$otp}}
</x-mail::panel>

This code will expire in 5 minutes.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
