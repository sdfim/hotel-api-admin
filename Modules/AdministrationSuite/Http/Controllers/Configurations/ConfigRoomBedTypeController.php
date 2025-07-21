<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigRoomBedType;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigRoomBedTypeController extends BaseWithPolicyController
{
    protected static string $model = ConfigRoomBedType::class;

    private array $message = ['edit' => 'Edit Room Bed Type', 'create' => 'Create Room Bed Type'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.room_bed_types.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configRoomBedType = ConfigRoomBedType::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.room_bed_types.form', compact('configRoomBedType', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configRoomBedType = new ConfigRoomBedType();
        $text = $this->message;

        return view('dashboard.configurations.room_bed_types.form', compact('configRoomBedType', 'text'));
    }
}
