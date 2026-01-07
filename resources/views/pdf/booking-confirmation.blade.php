@extends('pdf.vidanta_pdf_layout')

@section('content')
    <div class="hotel-info">
        <h1>Booking Confirmation</h1>
        <h2>{{ $hotelData['name'] ?? 'Hotel Name' }}</h2>
        <div class="address">{{ $hotelData['address'] ?? '' }}</div>
    </div>

    @if($hotelPhotoPath)
        <img src="{{ $hotelPhotoPath }}" class="hero-image">
    @endif

    {{-- Обновлены inline-стили для соответствия новой теме --}}
    <div style="margin-bottom: 30px; padding: 25px; border-left: 4px solid #C29C75; background-color: #FBF9F6;">
        <p style="font-family: 'Georgia', serif; font-size: 18px; color: #1C1B1B; margin: 0;">Dear {{ $customerName ?? 'Guest' }},</p>
        <p style="font-size: 13px; margin: 10px 0 0 0; line-height: 1.5;">We are delighted to confirm your upcoming stay. Below is a summary
            of your itinerary.</p>
    </div>

    <div class="section-title">Financial Summary</div>
    <table class="pricing-table" cellspacing="0" cellpadding="0">
        <tr>
            <td>Total Net</td>
            <td align="right">${{ number_format($total_net ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Total Tax</td>
            <td align="right">${{ number_format($total_tax ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Total Fees</td>
            <td align="right">${{ number_format($total_fees ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="total">Total Price (USD)</td>
            <td align="right" class="total" style="color: #C29C75;">${{ number_format($total_price ?? 0, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top: 30px;">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="50%">
                    <div class="label">Booking Agent</div>
                    <div class="value" style="font-size: 14px; color: #1C1B1B;">{{ $agency['booking_agent'] ?? '' }}</div>
                    <div style="font-size: 12px; color: #888; line-height: 1.4;">{{ $agency['booking_agent_email'] ?? '' }}</div>
                </td>
                <td width="50%" align="right" style="vertical-align: bottom;">
                    <div class="label">Date Issued</div>
                    <div class="value" style="font-size: 14px; color: #1C1B1B;">{{ date('d F Y') }}</div>
                </td>
            </tr>
        </table>
    </div>
@endsection
