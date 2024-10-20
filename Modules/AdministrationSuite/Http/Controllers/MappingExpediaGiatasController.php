<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Mapping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class MappingExpediaGiatasController extends BaseWithPolicyController
{
    protected static string $model = Mapping::class;

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $expedia_id = $request->get('expedia_id');
        $giata_id = $request->get('giata_id');
        $giata_last_id = $request->get('giata_last_id') ?? null;
        $mapper = Mapping::expedia()->where('supplier_id', $expedia_id)->where('giata_id', $giata_last_id)->first();

        if (is_null($giata_id)) {
            DB::table('mappings')
                ->where('supplier_id', $expedia_id)
                ->where('giata_id', $giata_last_id)
                ->where('supplier', MappingSuppliersEnum::Expedia->value)
                ->delete();
        } elseif ($mapper) {
            DB::table('mappings')
                ->where('supplier_id', $expedia_id)
                ->where('giata_id', $giata_last_id)
                ->where('supplier', MappingSuppliersEnum::Expedia->value)
                ->update(['giata_id' => $giata_id]);
        } else {
            Mapping::create([
                'supplier_id' => $expedia_id,
                'giata_id' => $giata_id,
                'supplier' => MappingSuppliersEnum::Expedia->value,
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
        $channels = Mapping::expedia()->where('supplier_id', $id)->firstOrFail();
        $channels->delete();

        return redirect()->route('expedia.index')
            ->with('success', 'Mapping deleted successfully');
    }
}
