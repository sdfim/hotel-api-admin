<?php

namespace Modules\Insurance\Http\Controllers;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;
use Modules\Insurance\Models\InsuranceRateTier;

class InsuranceRateTiersController extends Controller
{
    private array $message = ['create' => 'Add New Rate Tier', 'edit' => 'Edit Rate Tier'];

    public function index(): View
    {
        return view('dashboard.insurance.rate-tiers.index');
    }

    public function create(): View
    {
        $text = $this->message;

        return view('dashboard.insurance.rate-tiers.create', compact('text'));
    }

    public function edit(string $id): View
    {
        $text = $this->message;

        $insuranceRateTier = InsuranceRateTier::findOrFail($id);

        return view('dashboard.insurance.rate-tiers.edit', compact('insuranceRateTier', 'text'));
    }
}
