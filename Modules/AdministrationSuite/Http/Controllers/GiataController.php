<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\GiataProperty;

class GiataController extends Controller
{
    private $message = ['show' => 'Show Giata'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.giata.index');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $text = $this->message;
        $giata = GiataProperty::where('code', $id)->first();
        return view('dashboard.giata.show', compact('giata', 'text'));
    }
}
