<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\GiataProperty;
use Illuminate\Contracts\View\View;

class GiataController extends Controller
{
    /**
     * @var array|string[]
     */
    private array $message = ['show' => 'Show Giata'];

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
    public function show(string $id): View
    {
        $text = $this->message;
        $giata = GiataProperty::where('code', $id)->first();

        return view('dashboard.giata.show', compact('giata', 'text'));
    }
}
