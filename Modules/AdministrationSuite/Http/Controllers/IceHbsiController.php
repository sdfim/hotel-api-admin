<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Contracts\View\View;

class IceHbsiController extends Controller
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
        return view('dashboard.ice-hbsi.index');
    }
}
