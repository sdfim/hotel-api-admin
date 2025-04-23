<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigAttributeCategory;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigAttributeCategoryController extends BaseWithPolicyController
{
    protected static string $model = ConfigAttributeCategory::class;

    private array $message = ['edit' => 'Edit Category', 'create' => 'Create Category'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.attributes.category_index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configAttributeCategory = ConfigAttributeCategory::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.attributes.category_form', compact('configAttributeCategory', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configAttributeCategory = new ConfigAttributeCategory();
        $text = $this->message;

        return view('dashboard.configurations.attributes.category_form', compact('configAttributeCategory', 'text'));
    }
}
