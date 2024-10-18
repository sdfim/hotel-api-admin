<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Role;
use Illuminate\View\View;

class RolesController extends Controller
{
    private array $message = ['edit' => 'Edit Role', 'create' => 'Create Role'];
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.roles.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $role = Role::findOrFail($id);
        $text = $this->message;

        return view('dashboard.roles.form', compact('role', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $role = new Role();
        $text = $this->message;

        return view('dashboard.roles.form', compact('role', 'text'));
    }
}
