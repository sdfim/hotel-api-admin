<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\MappingRoom;
use Illuminate\Contracts\View\View;

class MappingRoomController extends BaseWithPolicyController
{
    protected static string $model = MappingRoom::class;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.mapping.mapping-room.index');
    }
}
