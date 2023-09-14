<?php

namespace App\Http\Controllers;

use App\Models\Channels;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfigurationChannelsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        $pageCount = 5;
        $channels = Channels::latest()->paginate($pageCount);
        return view('channels.index', compact('channels'))->with('1', (request()->input('page', 1) - 1) * $pageCount);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('channels.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        Channels::create($request->all());

        return redirect()->route('channels.index')
            ->with('success', 'Channels created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Channels $channels): View
    {
        return view('channels.show', compact('channels'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Channels $channels): View
    {
        return view('channels.edit', compact('channels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Channels $channels): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'detail' => 'required',
        ]);

        $channels->update($request->all());

        return redirect()->route('channels.index')
            ->with('success', 'Channels updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Channels $channels): RedirectResponse
    {
        $channels->delete();

        return redirect()->route('channels.index')
            ->with('success', 'Channels deleted successfully');
    }
}
