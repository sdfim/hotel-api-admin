<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Suppliers;
use App\Models\Weights;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WeightController extends Controller
{
    private $validate = [
        'property' => 'bail|required|integer',
        'supplier_id' => 'bail|nullable|integer',
        'weight' => 'bail|required|integer',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index (): View
    {
        return view('dashboard.weight.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create (): View
    {
        $suppliers = Suppliers::all();
        $array_suppliers = ['' => 'Select supplier'];
        foreach ($suppliers as $supplier) {
            $array_suppliers += [$supplier->id => $supplier->name];
        }

        return view('dashboard.weight.create', [
            'suppliers' => $array_suppliers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store (Request $request): RedirectResponse
    {
        $request->validate($this->validate);
        Weights::create($request->all());

        return redirect()->route('weight.index')->with('success', 'Weight created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show (string $id): View
    {
        $weight = Weights::findOrFail($id);

        return view('dashboard.weight.show', compact('weight'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit (string $id): View
    {
        $weight = Weights::findOrFail($id);
        $suppliers = Suppliers::all();
        $array_suppliers = ['' => 'Select supplier'];
        foreach ($suppliers as $supplier) {
            $array_suppliers += [$supplier->id => $supplier->name];
        }
        return view('dashboard.weight.edit', compact(['weight', 'array_suppliers']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update (Request $request, string $id): RedirectResponse
    {
        $suppliers = Weights::findOrFail($id);
        $request->validate($this->validate);
        $suppliers->update($request->all());

        return redirect()->route('weight.index')
            ->with('success', 'Weight updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy (string $id): RedirectResponse
    {
        $suppliers = Weights::findOrFail($id);
        $suppliers->delete();

        return redirect()->route('weight.index')
            ->with('success', 'Weight deleted successfully');
    }
}
