<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UsersController extends BaseWithPolicyController
{
    protected static string $model = User::class;

    private array $message = ['edit' => 'Edit User', 'create' => 'Create User'];

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

        return view('dashboard.users.form', compact('user', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = new User();
        $text = $this->message;

        return view('dashboard.users.form', compact('user', 'text'));
    }
}
