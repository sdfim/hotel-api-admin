<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Suppliers;
use App\Models\PropertyWeighting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyWeightingController extends Controller
{
    private array $message = ['create' => 'Add New Weight', 'edit' => 'Edit Weight', 'show' => 'Show Weight'];
    private array $validate = [
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
            'suppliers' => $array_suppliers, 'text' => $this->message
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store (Request $request): RedirectResponse
    {
        $request->validate($this->validate);
        PropertyWeighting::create($request->all());

        return redirect()->route('weight.index')->with('success', 'Weight created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show (string $id): View
    {
        $text = $this->message;
        $weight = PropertyWeighting::findOrFail($id);

        return view('dashboard.weight.show', compact('weight', 'text'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit (string $id): View
    {
        $weight = PropertyWeighting::findOrFail($id);
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
        $suppliers = PropertyWeighting::findOrFail($id);
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
        $suppliers = PropertyWeighting::findOrFail($id);
        $suppliers->delete();

        return redirect()->route('weight.index')
            ->with('success', 'Weight deleted successfully');
    }
}
