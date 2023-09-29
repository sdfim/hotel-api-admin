<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Suppliers;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SuppliersController extends Controller
{
    private $message = ['create' => 'Add New Suppliers', 'edit' => 'Edit Suppliers', 'show' => 'Show Suppliers'];

    private $validate = [
        'name' => 'bail|required|string|max:190',
        'description' => 'bail|required|string|max:190'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index (): View
    {
        return view('dashboard.suppliers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create ()
    {
        $text = $this->message;
        $suppliers = Suppliers::all();
        return view('dashboard.suppliers.create', compact('suppliers', 'text'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store (Request $request): RedirectResponse
    {
        $request->validate($this->validate);
        Suppliers::create($request->all());

        return redirect()->route('suppliers.index')->with('success', 'Suppliers created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show (string $id): View
    {
        $text = $this->message;
        $suppliers = Suppliers::findOrFail($id);

        return view('dashboard.suppliers.show', compact('suppliers', 'text'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit (string $id): View
    {
        $text = $this->message;
        $suppliers = Suppliers::findOrFail($id);

        return view('dashboard.suppliers.edit', compact('suppliers', 'text'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update (Request $request, string $id): RedirectResponse
    {
        $suppliers = Suppliers::findOrFail($id);
        $request->validate($this->validate);
        $suppliers->update($request->all());

        return redirect()->route('suppliers.index')
            ->with('success', 'Pricing rule updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy (string $id): RedirectResponse
    {
        $suppliers = Suppliers::findOrFail($id);
        $suppliers->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Pricing rule deleted successfully');
    }
}
