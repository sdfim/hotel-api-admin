<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\GeneralConfiguration;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GeneralConfigurationController extends Controller
{
    public function index(): View
    {
        $result = GeneralConfiguration::first();

        $general_configuration = $result ?: new GeneralConfiguration();
        Gate::authorize('view', $general_configuration);

        return view('dashboard.general-configuration', [
            'general_configuration' => $general_configuration,
        ]);
    }
}
