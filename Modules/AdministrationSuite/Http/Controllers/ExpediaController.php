<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\ExpediaContent;

class ExpediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():View
    {
        //
        $epx = ExpediaContent::where('property_id',1846741)->with('mapperGiataExpedia')->get();
        dd($epx->);
        // $expedia = ExpediaContent::leftJoin('mapper_expedia_giatas', 'expedia_contents.property_id', '=', 'mapper_expedia_giatas.expedia_id')->where('mapper_expedia_giatas.expedia_id',1846741)->limit(1)->get();
        // dd($expedia);
        return view('dashboard.expedia.index');

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
