<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\IcePortalPropery;
use Illuminate\Contracts\View\View;

class IceHbsiController extends BaseWithPolicyController
{
    protected static string $model = IcePortalPropery::class;

    /**
     * @var array|string[]
     */
    private array $message = ['show' => 'Show Giata'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.ice-hbsi.index');
    }
}
