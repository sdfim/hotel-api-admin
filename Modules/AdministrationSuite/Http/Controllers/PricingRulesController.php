<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\PricingRule;
use Illuminate\View\View;

class PricingRulesController extends BaseWithPolicyController
{
    protected static string $model = PricingRule::class;

    private array $message = ['create' => 'Add New Pricing Rules', 'edit' => 'Edit Pricing Rules', 'show' => 'Show Pricing Rules'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.pricing-rules.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $text = $this->message;

        return view('dashboard.pricing-rules.create', compact('text'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $text = $this->message;

        $pricingRule = PricingRule::with('conditions')->findOrFail($id);
        $conditions = $pricingRule->conditions;

        return view('dashboard.pricing-rules.show', compact('pricingRule', 'conditions', 'text'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $text = $this->message;

        $pricingRule = PricingRule::findOrFail($id);

        return view('dashboard.pricing-rules.update', compact('pricingRule', 'text'));
    }
}
