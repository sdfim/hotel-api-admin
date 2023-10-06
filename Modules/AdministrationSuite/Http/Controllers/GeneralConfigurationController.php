<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralConfiguration;

class GeneralConfigurationController extends Controller
{
    public function index()
    {
        $result = GeneralConfiguration::first();

        $general_configuration = $result ? $result : new GeneralConfiguration();

        return view('dashboard.general-configuration', [
            'general_configuration' => $general_configuration,
        ]);
    }
}
