<x-mail::message>
# Confirm your booking

Please confirm your booking by clicking the link below:

<x-mail::button :url="$verificationUrl">
Confirm Booking
</x-mail::button>

If you did not make this booking, simply ignore this email.

Thank you!<br>
{{ config('app.name') }}
</x-mail::message>
