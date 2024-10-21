<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;
use Modules\Insurance\Models\InsuranceRestriction;

class InsuranceRestrictionsController extends Controller
{
	private array $message = ['create' => 'Add New Restriction', 'edit' => 'Edit Restriction'];

	public function index(): View
	{
		return view('dashboard.insurance.restrictions.index');
	}

	public function create(): View
	{
		$text = $this->message;

		return view('dashboard.insurance.restrictions.create', compact('text'));
	}

	public function edit(string $id): View
	{
		$text = $this->message;

		$insuranceRestriction = InsuranceRestriction::findOrFail($id);

		return view('dashboard.insurance.restrictions.edit', compact('insuranceRestriction', 'text'));
	}
}

