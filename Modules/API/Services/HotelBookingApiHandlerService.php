<?php

namespace Modules\API\Services;

use App\Models\User;
use Illuminate\Support\Arr;

class HotelBookingApiHandlerService
{
    public function getApiUserDataByRequest($request): array
    {
        $apiClientId = Arr::get($request, 'api_client.id');
        $apiClientEmail = Arr::get($request, 'api_client.email');

        // Determine missing api client info from User model
        if (filled($apiClientId) && empty($apiClientEmail)) {
            $user = User::find($apiClientId);
            if ($user) {
                $apiClientEmail = $user->email;
            }
        }
        if (filled($apiClientEmail) && empty($apiClientId)) {
            $user = User::where('email', $apiClientEmail)->first();
            if ($user) {
                $apiClientId = $user->id;
            }
        }

        return [$apiClientEmail, $apiClientId];
    }

    public function refreshFiltersByApiUser(&$filters, $request)
    {
        [$apiClientEmail, $apiClientId] = $this->getApiUserDataByRequest($request);
        if (filled($apiClientEmail)) {
            $filters['api_client']['email'] = $apiClientEmail;
        }
        if (filled($apiClientId)) {
            $filters['api_client']['id'] = $apiClientId;
        }
    }
}
