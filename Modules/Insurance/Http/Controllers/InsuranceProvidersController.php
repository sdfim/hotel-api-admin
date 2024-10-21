<?php

namespace Modules\Insurance\Http\Controllers;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;
use Modules\Insurance\Models\InsuranceProvider;

class InsuranceProvidersController extends Controller
{
    private array $message = ['create' => 'Add New Provider', 'edit' => 'Edit Provider'];

    public function index(): View
    {
        return view('dashboard.insurance.providers.index');
    }

    public function create(): View
    {
        $text = $this->message;

        return view('dashboard.insurance.providers.create', compact('text'));
    }

    public function edit(string $id): View
    {
        $text = $this->message;

        $insuranceProvider = InsuranceProvider::findOrFail($id);

        return view('dashboard.insurance.providers.edit', compact('insuranceProvider', 'text'));
    }
}
