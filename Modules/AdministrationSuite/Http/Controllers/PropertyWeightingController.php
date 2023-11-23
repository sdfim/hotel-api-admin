<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\PropertyWeighting;
use Illuminate\Contracts\View\View;

class PropertyWeightingController extends Controller
{
    /**
     * @var array|string[]
     */
    private array $message = ['create' => 'Add New Weight', 'edit' => 'Edit Weight', 'show' => 'Show Weight'];

    /**
     * Display a listing of the property weightings.
     */
    public function index(): View
    {
        return view('dashboard.property-weighting.index');
    }

    /**
     * Show the form for creating a new property weighting.
     */
    public function create(): View
    {
        return view('dashboard.property-weighting.create', ['text' => $this->message]);
    }

    /**
     * Display the specified property weighting.
     */
    public function show(string $id): View
    {
        $text = $this->message;
        $propertyWeighting = PropertyWeighting::findOrFail($id);

        return view('dashboard.property-weighting.show', compact(['propertyWeighting', 'text']));
    }

    /**
     * Show the form for editing the specified property weighting.
     */
    public function edit(string $id): View
    {
        $text = $this->message;
        $propertyWeighting = PropertyWeighting::findOrFail($id);
        return view('dashboard.property-weighting.update', compact(['propertyWeighting', 'text']));
    }
}
