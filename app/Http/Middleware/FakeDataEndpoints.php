<?php

namespace App\Http\Middleware;

use App\Models\ApiBookingInspector;
use App\Models\ApiSearchInspector;
use App\Repositories\ChannelRenository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FakeDataEndpoints
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();
        $channelName = ChannelRenository::getTokenName($token);

        if ($channelName === 'FakeChannel') {
            $path = $request->path();

            $searchInspector = ApiSearchInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])->inRandomOrder()->first();

            $add_item = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'add_item')->inRandomOrder()->first();
            $add_passengers = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'add_passengers')->inRandomOrder()->first();
            $retrieve_items = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'retrieve_booking')->inRandomOrder()->first();
            $remove_item = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'remove_item')->inRandomOrder()->first();
            $book = ApiBookingInspector::whereBetween('created_at', ['2023-12-16', '2024-01-03'])
                ->where('type', 'book')->where('sub_type', 'retrieve')->inRandomOrder()->first();
//            dd($add_passengers);
            switch ($path) {
                case 'api/pricing/search': return response()->json(json_decode(Storage::get($searchInspector->client_response_path), true));

                case 'api/booking/add-item': return response()->json(json_decode(Storage::get($add_item->response_path), true));
                case 'api/booking/retrieve-items': return response()->json(json_decode(Storage::get($retrieve_items->client_response_path), true));
                case 'api/booking/remove-item': return response()->json(json_decode(Storage::get($remove_item->client_response_path), true));
                case 'api/booking/add-passengers': return response()->json(json_decode(Storage::get($add_passengers->client_response_path), true));

                case 'api/booking/book': return response()->json(json_decode(Storage::get($book->client_response_path), true));
            }
        }

        return $next($request);
    }
}
