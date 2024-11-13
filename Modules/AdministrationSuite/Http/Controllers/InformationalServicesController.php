<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\InformationalService;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Illuminate\Contracts\View\View;

class InformationalServicesController extends BaseWithPolicyController
{
    protected static string $model = InformationalService::class;

    private array $message = [
        'create' => 'Create Informational Service',
        'edit' => 'Edit Informational Service',
    ];

    public function index(): View
    {
        return view('dashboard.informational-services.index');
    }

    public function create(): View
    {
        $text = $this->message;
        $service = new InformationalService();

        return view('dashboard.informational-services.form', compact('service','text'));
    }

    public function edit(string $id): View
    {
        $text = $this->message;
        $service = InformationalService::findOrFail($id);

        return view('dashboard.informational-services.form', compact('service', 'text'));
    }
}
