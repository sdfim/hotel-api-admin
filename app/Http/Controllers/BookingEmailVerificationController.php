<?php

namespace App\Http\Controllers;

use App\Models\ApiBookingItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BookingEmailVerificationController extends Controller
{
    public function verify($booking_item, $uuid): View
    {
        $item = ApiBookingItem::where('booking_item', $booking_item)->first();
        if (! $item) {
            return view('booking.email_verification_notfound');
        }
        if ($item->email_verified) {
            return view('booking.email_verification_already_verified');
        }

        $cacheKey = 'booking_email_verification:'.$booking_item.':'.$uuid;
        $cachedBookingItem = Cache::get($cacheKey);
        if (! $cachedBookingItem || $cachedBookingItem !== $booking_item) {
            return view('booking.email_verification_notfound');
        }

        $item->email_verified = true;
        $item->save();
        // Optionally, remove the cache entry after successful verification
        Cache::forget($cacheKey);
        Log::info('Booking item email verified: '.$booking_item);

        return view('booking.email_verification_thankyou');
    }
}
