<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;
use Modules\Insurance\Models\InsuranceProvider;

class InsuranceProvidersController extends BaseWithPolicyController
{
    protected static string $model = InsuranceProvider::class;

	private array $message = ['create' => 'Add New Provider', 'edit' => 'Edit Provider'];

	public function index(): View
	{
		return view('dashboard.insurance.providers.index');
	}

	public function create(): View
	{
		$text = $this->message;

		return view('dashboard.insurance.providers.create', compact('text'));
	}

	public function edit(string $id): View
	{
		$text = $this->message;

		$insuranceProvider = InsuranceProvider::findOrFail($id);

		return view('dashboard.insurance.providers.edit', compact('insuranceProvider', 'text'));
	}
}
