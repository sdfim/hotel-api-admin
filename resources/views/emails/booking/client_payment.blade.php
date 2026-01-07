@extends('emails.vidanta_layout')

@section('title', env('APP_NAME') . ' – Secure Payment Request')

@section('content')
    <h1 style="font-size: 32px; line-height: 40px; margin-bottom: 25px; text-align: center; font-style: italic;">
        Finalizing Your Stay
    </h1>

    <p style="text-align: center; margin-bottom: 40px; font-size: 16px;">
        Hello, we’re delighted to finalize the arrangements for your upcoming stay at <strong>{{ $hotelName }}</strong>.
    </p>

    <div
        style="margin-bottom: 50px; padding: 40px; background-color: #FBF9F6; border: 1px solid #EAEAEA; text-align: center;">
        <p style="font-size: 15px; margin-bottom: 30px; line-height: 26px;">
            To complete your booking and secure your reservation, please use the button below to access our secure payment
            portal. We kindly request that you provide your details within 24 hours.
        </p>

        <table role="presentation" align="center" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td align="center">
                    <a href="{{ $payment_url }}"
                        style="background-color: #1C1B1B; color: #FFFFFF; padding: 18px 45px; font-family: 'Montserrat', sans-serif; font-size: 14px; font-weight: 500; letter-spacing: 2px; text-transform: uppercase; display: inline-block;">
                        Secure Payment
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <p style="text-align: center; margin-bottom: 40px; line-height: 26px;">
        Your security is our priority. All transactions are processed through encrypted channels. Should you encounter any
        issues, our team is standing by to help.
    </p>

    <div style="text-align: center; padding-top: 30px; border-top: 1px solid #EAEAEA;">
        <h3 style="font-size: 20px; margin-bottom: 10px; font-style: italic;">Thank you for your trust</h3>
        <p style="font-size: 13px; color: #888;">{{ env('APP_NAME') }} Concierge</p>
    </div>
@endsection
