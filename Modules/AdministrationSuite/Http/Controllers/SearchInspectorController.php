<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\ApiSearchInspector;
use Illuminate\View\View;

class SearchInspectorController extends Controller
{
    private array $message = ['show' => 'Show Response'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.search-inspector.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $text = $this->message;
        $inspector = ApiSearchInspector::findOrFail($id);
        return view('dashboard.search-inspector.show', compact('inspector', 'text'));
    }

}
