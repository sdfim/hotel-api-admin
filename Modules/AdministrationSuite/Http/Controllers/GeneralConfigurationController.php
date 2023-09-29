<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralConfiguration;

class GeneralConfigurationController extends Controller
{
    public function index ()
    {
        return view('dashboard.general-configuration', [
            'general_configuration' => GeneralConfiguration::first(),
        ]);
    }

    public function save (Request $request)
    {
        $general_configuration = GeneralConfiguration::get();
        if (count($general_configuration) == 0) {
            $general_configuration_row = new GeneralConfiguration();
            $general_configuration_row->time_supplier_requests = $request->time_supplier_requests;
            $general_configuration_row->time_reservations_kept = $request->time_reservations_kept;
            $general_configuration_row->currently_suppliers = $request->currently_suppliers;
            $general_configuration_row->time_inspector_retained = $request->time_inspector_retained;
            $general_configuration_row->star_ratings = $request->star_ratings;
            $general_configuration_row->stop_bookings = $request->stop_bookings;
            //$general_configuration_row->channel_id = 1; // ВЫБРАТЬ НУЖНЫЙ АЙДИ
            $general_configuration_row->save();
        } else {
            $general_configuration[0]->time_supplier_requests = $request->time_supplier_requests;
            $general_configuration[0]->time_reservations_kept = $request->time_reservations_kept;
            $general_configuration[0]->currently_suppliers = $request->currently_suppliers;
            $general_configuration[0]->time_inspector_retained = $request->time_inspector_retained;
            $general_configuration[0]->star_ratings = $request->star_ratings;
            $general_configuration[0]->stop_bookings = $request->stop_bookings;
            $general_configuration[0]->save();
        }
        return back();
    }
}
