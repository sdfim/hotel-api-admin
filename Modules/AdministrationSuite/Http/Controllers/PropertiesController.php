<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Property;
use Illuminate\Contracts\View\View;

class PropertiesController extends Controller
{
    /**
     * @var array|string[]
     */
    private array $message = ['show' => 'Show Properties'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.properties.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $text = $this->message;
        $property = Property::where('code', $id)->first();

        return view('dashboard.properties.show', compact('property', 'text'));
    }
}
