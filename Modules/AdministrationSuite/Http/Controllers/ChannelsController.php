<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelsController extends Controller
{

    /**
     * @var array|string[]
     */
    private array $message = ['create' => 'Add New Channel', 'edit' => 'Edit Channel', 'show' => 'Show Channel'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.channels.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $text = $this->message;

        return view('dashboard.channels.create', compact('text'));
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

        $token = auth()->user()->createToken($request->get('name'));

        $channel = [
            'token_id' => $token->accessToken->id,
            'access_token' => $token->plainTextToken,
            'name' => $request->get('name'),
            'description' => $request->get('description'),
        ];

        Channel::create($channel);

        return redirect()->route('channels.index')
            ->with('success', 'Channels created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $channel = Channel::findOrFail($id);
        $text = $this->message;

        return view('dashboard.channels.show', compact('channel', 'text'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $channel = Channel::findOrFail($id);
        $text = $this->message;

        return view('dashboard.channels.edit', compact('channel', 'text'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $channel = Channel::findOrFail($id);
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
    public function destroy(string $id): RedirectResponse
    {
        $channels = Channel::findOrFail($id);
        $channels->delete();

        return redirect()->route('channels.index')
            ->with('success', 'Channels deleted successfully');
    }
}
