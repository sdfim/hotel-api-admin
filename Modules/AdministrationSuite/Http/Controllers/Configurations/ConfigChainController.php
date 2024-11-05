<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigChain;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigChainController extends BaseWithPolicyController
{
    protected static string $model = ConfigChain::class;

    private array $message = ['edit' => 'Edit Chain', 'create' => 'Create Chain'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.chains.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configChain = ConfigChain::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.chains.form', compact('configChain', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configChain = new ConfigChain();
        $text = $this->message;

        return view('dashboard.configurations.chains.form', compact('configChain', 'text'));
    }
}
