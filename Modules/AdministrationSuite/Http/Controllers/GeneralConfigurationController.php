<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\GeneralConfiguration;
use Illuminate\View\View;

class GeneralConfigurationController extends Controller
{
    public function index(): View
    {
        $result = GeneralConfiguration::first();

        $general_configuration = $result ?: new GeneralConfiguration();

        return view('dashboard.general-configuration', [
            'general_configuration' => $general_configuration,
        ]);
    }
}
