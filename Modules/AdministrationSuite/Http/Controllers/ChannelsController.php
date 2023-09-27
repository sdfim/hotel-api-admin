<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Channels;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ChannelsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index (Request $request): View
    {
        $perPage = $request->query('perPage') ?? 10;
        $page = $request->query('page') ?? 1;

        $channels = Channels::skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $totalItems = Channels::count();

        $paginator = new LengthAwarePaginator($channels, $totalItems, $perPage, $page);
        $paginator->setPath(route('channels.index'));

        return view('dashboard.channels.index', compact('channels', 'paginator'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create (): View
    {
        return view('dashboard.channels.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store (Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:190',
            'description' => 'required|string|max:190',
        ]);

        $token = auth()->user()->createToken($request->get('name'));

        $channel = [
            'token_id' => $token->accessToken->id,
            'access_token' => $token->plainTextToken,
            'name' => $request->get('name'),
            'description' => $request->get('description'),
        ];

        Channels::create($channel);

        return redirect()->route('channels.index')
            ->with('success', 'Channels created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show (string $id): View
    {
        $channel = Channels::findOrFail($id);

        return view('dashboard.channels.show', compact('channel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit (string $id): View
    {
        $channel = Channels::findOrFail($id);

        return view('dashboard.channels.edit', compact('channel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update (Request $request, string $id): RedirectResponse
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
    public function destroy (string $id): RedirectResponse
    {
        $channels = Channels::findOrFail($id);
        $channels->delete();

        return redirect()->route('channels.index')
            ->with('success', 'Channels deleted successfully');
    }
}
