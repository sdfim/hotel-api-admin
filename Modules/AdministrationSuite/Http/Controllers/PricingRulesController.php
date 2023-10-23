<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\PricingRule;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PricingRulesController extends Controller
{
    private array $message = ['create' => 'Add New Pricing Rules', 'edit' => 'Edit Pricing Rules', 'show' => 'Show Pricing Rules'];

    private array $validate = [
        'name' => 'bail|required|string|max:190',
        'property' => 'bail|required|string|max:190',
        'destination' => 'bail|required|string|max:190',
        'travel_date' => 'bail|required', //TODO validate datetime
        'days' => 'bail|required|max:12',
        'nights' => 'bail|required|integer|gt:0',
        'supplier_id' => 'bail|required|exists:suppliers,id',
        'rate_code' => 'bail|required|string|max:190',
        'room_type' => 'bail|required|string|max:190',
        'total_guests' => 'bail|required|integer|gt:0',
        'room_guests' => 'bail|required|integer|gt:0',
        'number_rooms' => 'bail|required|integer|gt:0',
        'meal_plan' => 'bail|required|string|max:190',
        'rating' => 'bail|required|string|max:190'
    ];

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
        $suppliers = Supplier::all()->pluck('name', 'id')->toArray();
        return view('dashboard.pricing-rules.create', compact('suppliers', 'text'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->validate);
        PricingRule::create($request->all());

        return redirect()->route('pricing_rules.index')->with('success', 'Pricing rule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $text = $this->message;
        $pricingRule = PricingRule::findOrFail($id);

        return view('dashboard.pricing-rules.show', compact('pricingRule', 'text'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $text = $this->message;
        $pricingRules = PricingRule::findOrFail($id);
        return view('dashboard.pricing-rules.update', compact('pricingRules', 'text'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $pricingRules = PricingRule::findOrFail($id);
        $request->validate($this->validate);
        $pricingRules->update($request->all());

        return redirect()->route('pricing_rules.index')
            ->with('success', 'Pricing rule updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $pricingRules = PricingRule::findOrFail($id);
        $pricingRules->delete();

        return redirect()->route('pricing_rules.index')
            ->with('success', 'Pricing rule deleted successfully');
    }
}
