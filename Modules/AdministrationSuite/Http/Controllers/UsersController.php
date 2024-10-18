<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UsersController extends Controller
{
    private array $message = ['edit' => 'Edit User'];
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $user = User::findOrFail($id);
        $text = $this->message;

        return view('dashboard.users.edit', compact('user', 'text'));
    }
}
