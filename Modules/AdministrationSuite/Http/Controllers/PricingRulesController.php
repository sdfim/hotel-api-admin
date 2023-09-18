<?php

namespace App\Http\Controllers;

use App\Models\ManipulateData;
use App\Models\ManipulateItem;
use App\Models\ManipulateType;
use App\Models\PricingRules;
use App\Models\Suppliers;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PricingRulesController extends Controller
{

    private $validate = [
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
        'rating' => 'bail|required|string|max:190',
        'manipulate_data_id' => 'bail|required|exists:manipulate_data,id',
        'manipulate_item_id' => 'bail|required|exists:manipulate_item,id',
        'manipulate_type_id' => 'bail|required|exists:manipulate_type,id',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $pageCount = 2;
        $pricingRules = PricingRules::with(['suppliers', 'manipulateData', 'manipulateType', 'manipulateItem'])->get();
        $pricingRules = PricingRules::latest()->paginate($pageCount);
        // echo '<pre>', $pricingRules, '</pre>';
        // die;
        $startNumber = ($pricingRules->currentPage() - 1) * $pricingRules->perPage() + 1;
        return view('pricingRules.index', compact('pricingRules', 'startNumber'))->with('1', (request()->input('page', 1) - 1) * $pageCount);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Suppliers::all();
        $manipulateDatas = ManipulateData::all();
        $manipulateTypes = ManipulateType::all();
        $manipulateItems = ManipulateItem::all();

        return view('pricingRules.create', compact('suppliers', 'manipulateDatas', 'manipulateTypes', 'manipulateItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->validate);
        PricingRules::create($request->all());
        /* var_dump('pre', $request->input());
        die; */
        return redirect()->route('pricing-rules.index')->with('success', 'Pricing rule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id): View
    {
        $pricingRule = PricingRules::findOrFail($id);

        return view('pricingRules.show', compact('pricingRule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id): View
    {
        $pricingRule = PricingRules::findOrFail($id);
        $suppliers = Suppliers::all();
        $manipulateData = ManipulateData::all();
        $manipulateTypes = ManipulateType::all();
        $manipulateItems = ManipulateItem::all();

        return view('pricing-rules.edit', compact('pricingRule', 'suppliers', 'manipulateData', 'manipulateTypes', 'manipulateItems'));

        // return view('pricingRules.edit', compact('pricingRule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id): RedirectResponse
    {
        $pricingRules = PricingRules::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:190',
            'description' => 'required|string|max:190',
        ]);
        $pricingRules->update($request->all());

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Pricing rule updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id): RedirectResponse
    {
        $pricingRules = PricingRules::findOrFail($id);
        $pricingRules->delete();

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Pricing rule deleted successfully');
    }
}
