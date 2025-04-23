<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended(function () {
                if (auth()->user()->can('vendor.create')) {

                    return route('vendor-repository');
                } else {
                    session()->flush();
                    Cookie::queue(Cookie::forget('XSRF-TOKEN'));
                    Cookie::queue(Cookie::forget('laravel_session'));

                    return route('login');
                }
            });
    }
}
