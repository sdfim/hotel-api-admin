<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\Commission;

class ConfigCommissionController extends BaseWithPolicyController
{
    protected static string $model = Commission::class;

    private array $message = ['edit' => 'Edit Commission', 'create' => 'Create Commission'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.commissions.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $commission = Commission::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.commissions.form', compact('commission', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $commission = new Commission;
        $text = $this->message;

        return view('dashboard.configurations.commissions.form', compact('commission', 'text'));
    }
}
