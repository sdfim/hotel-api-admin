<?php

namespace App\Http\Controllers;

use App\Models\PricingRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PricingRulesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $pageCount = 2;

        $pricingRules = PricingRules::latest()->paginate($pageCount);
        $startNumber = ($pricingRules->currentPage() - 1) * $pricingRules->perPage() + 1;
        return view('pricingRules.index', compact('pricingRules', 'startNumber'))->with('1', (request()->input('page', 1) - 1) * $pageCount);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pricingRules.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        var_dump('pre',$request->input());die;
        $request->validate([
            'name' => 'required|string|max:190',
            'description' => 'required|string|max:190',
        ]);

         

        return redirect()->route('channels.index')
            ->with('success', 'Channels created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingRules $pricingRules)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PricingRules $pricingRules)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PricingRules $pricingRules)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingRules $pricingRules)
    {
        //
    }
}
