<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\ApiSearchInspector;
use Illuminate\View\View;

class SearchInspectorController extends BaseWithPolicyController
{
    protected static string $model = ApiSearchInspector::class;

    /**
     * @var array|string[]
     */
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
