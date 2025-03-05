<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class ConfigKeyMappingOwnerController extends BaseWithPolicyController
{
    protected static string $model = KeyMappingOwner::class;

    private array $message = ['edit' => 'Edit External Identifier', 'create' => 'Create External Identifier'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.key-mapping-owners.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $keyMappingOwner = KeyMappingOwner::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.key-mapping-owners.form', compact('keyMappingOwner', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $keyMappingOwner = new KeyMappingOwner();
        $text = $this->message;

        return view('dashboard.configurations.key-mapping-owners.form', compact('keyMappingOwner', 'text'));
    }
}
