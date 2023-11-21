<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\MapperExpediaGiata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class MappingExpediaGiatasController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $expedia_id = $request->get('expedia_id');
        $giata_id = $request->get('giata_id');
        $giata_last_id = $request->get('giata_last_id') ?? null;
        $mapper = MapperExpediaGiata::where('expedia_id', $expedia_id)->where('giata_id', $giata_last_id)->first();

        // dd($request->get('expedia_id'), $request->get('giata_id'), $request->get('giata_last_id'));

		if (is_null($giata_id)) {
			DB::table('ujv_api.mapper_expedia_giatas')
				->where('expedia_id', $expedia_id)
				->where('giata_id', $giata_last_id)
				->delete();
		}
		elseif ($mapper) {
			DB::table('ujv_api.mapper_expedia_giatas')
				->where('expedia_id', $expedia_id)
				->where('giata_id', $giata_last_id)
				->update(['giata_id' => $giata_id]);
		} else {
			MapperExpediaGiata::create([
				'expedia_id' => $expedia_id,
				'giata_id' => $giata_id,
				'step' => 100,
			]);
		}
        
        return redirect()->back()->with('success', 'Mapping update successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        //
        $channels = MapperExpediaGiata::findOrFail($id);
        $channels->delete();

        return redirect()->route('expedia.index')
            ->with('success', 'Mapping deleted successfully');
    }
}
