<?php

namespace App\Http\Controllers;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\User;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class BookingEmailVerificationController extends Controller
{
    public function verify($booking_item, $uuid): View
    {
        $item = ApiBookingItem::where('booking_item', $booking_item)->first();

        if (! $item->exists()) {
            return view('booking.email_verification_notfound');
        }

        $inspector = ApiBookingInspector::where('booking_item', $booking_item)->first();
        if ($inspector === null) {
            return view('booking.email_verification_notfound');
        }

        if ($item->exists() && (bool) $item->email_verified === true) {
            return view('booking.email_verification_already_verified');
        }
        if ($item->exists() && (bool) $item->email_verified === false && ! is_null($item->email_verified)) {
            return view('booking.email_verification_already_denied');
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

//        // Send notification email to agent
//        [$agentEmail, $agentId, $externalAdvisorEmail] = ApiBookingInspectorRepository::getEmailAgentBookingItem($booking_item);
//        if ($agentEmail) {
//            try {
//                if (! Cache::has('bookingItem_no_mail_'.$booking_item)) {
//                    Mail::to($agentEmail)->queue(new \App\Mail\BookingAgentNotificationMail($booking_item));
//                }
//            } catch (\Exception $e) {
//                Log::error('Failed to send agent notification email for booking item '.$booking_item.': '.$e->getMessage());
//            }
//        }
//
//        $notificationEmails = User::find($agentId)?->notification_emails ?? [];
//        foreach ($notificationEmails as $email) {
//            if (empty($email)) {
//                continue;
//            }
//            try {
//                if (! Cache::has('bookingItem_no_mail_'.$booking_item)) {
//                    Mail::to($email)->queue(new \App\Mail\BookingAgentNotificationMail($booking_item));
//                }
//            } catch (\Exception $e) {
//                Log::error('Failed to send agent notification email for booking item '.$booking_item.': '.$e->getMessage(), ['email' => $email]);
//            }
//        }
//
//        logger('Booking item email verified: '.$booking_item, ['agent_email' => $agentEmail, 'agent_id' => $agentId, 'notification_emails' => $notificationEmails]);

        return view('booking.email_verification_thankyou');
    }

    public function deny($booking_item, $uuid): View
    {
        $item = ApiBookingItem::where('booking_item', $booking_item)->first();

        if (! $item->exists()) {
            return view('booking.email_verification_notfound');
        }
        if ($item->exists() && (bool) $item->email_verified === false && ! is_null($item->email_verified)) {
            return view('booking.email_verification_already_denied');
        }
        if ($item->exists() && (bool) $item->email_verified === true) {
            return view('booking.email_verification_already_verified');
        }

        $cacheKey = 'booking_email_verification:'.$booking_item.':'.$uuid;
        $cachedBookingItem = Cache::get($cacheKey);
        if (! $cachedBookingItem || $cachedBookingItem !== $booking_item) {
            return view('booking.email_verification_notfound');
        }

        $item->email_verified = false;
        $item->save();
        Cache::forget($cacheKey);
        Log::info('Booking item email denied: '.$booking_item);

        return view('booking.email_verification_denied');
    }
}
