<?php

namespace Modules\API\Channels\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\Channels\ChannelsApiHandler;

class ChannelsApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('channels')->name('api.channels.')->group(function () {
            Route::get('/', [ChannelsApiHandler::class, 'all'])->name('all');
            Route::post('/', [ChannelsApiHandler::class, 'add'])->name('add');
            Route::get('/{channel_id}', [ChannelsApiHandler::class, 'get'])->name('get');
            Route::put('/{channel_id}', [ChannelsApiHandler::class, 'edit'])->name('edit');
            Route::post('/{channel_id}/refresh-token', [ChannelsApiHandler::class, 'refreshToken'])->name('refreshToken');
            Route::delete('/{channel_id}', [ChannelsApiHandler::class, 'delete'])->name('delete');
        });
    }
}
