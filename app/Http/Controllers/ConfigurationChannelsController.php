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
        $pageCount = 2;

        $channels = Channels::latest()->paginate($pageCount);
        $startNumber = ($channels->currentPage() - 1) * $channels->perPage() + 1;
        return view('channels.index', compact('channels', 'startNumber'))->with('1', (request()->input('page', 1) - 1) * $pageCount);
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
            'name' => 'required|string|max:190',
            'description' => 'required|string|max:190',
        ]);

        Channels::create($request->all());

        return redirect()->route('channels.index')
            ->with('success', 'Channels created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id): View
    {
        $channel = Channels::findOrFail($id);

        return view('channels.show', compact('channel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(String $id): View
    {
        $channel = Channels::findOrFail($id);

        return view('channels.edit', compact('channel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id): RedirectResponse
    {
        $channel = Channels::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:190',
            'description' => 'required|string|max:190',
        ]);
        $channel->update($request->all());

        return redirect()->route('channels.index')
            ->with('success', 'Channels updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id): RedirectResponse
    {
        $channels = Channels::findOrFail($id);
        $channels->delete();

        return redirect()->route('channels.index')
            ->with('success', 'Channels deleted successfully');
    }
}
