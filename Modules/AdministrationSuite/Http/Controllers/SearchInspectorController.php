<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiSearchInspector;

class SearchInspectorController extends Controller
{
    private $message = ['show' => 'Show Response'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard.search-inspector.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $text = $this->message;
        $inspector = ApiSearchInspector::findOrFail($id);
        return view('dashboard.search-inspector.show', compact('inspector', 'text'));
    }

}
