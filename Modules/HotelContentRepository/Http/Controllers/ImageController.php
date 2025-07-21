<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\Image;

class ImageController extends BaseWithPolicyController
{
    protected static string $model = Image::class;

    private array $message = ['edit' => 'Edit Image', 'create' => 'Create Image'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.images.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $repositoryImage = Image::findOrFail($id);
        $text = $this->message;

        return view('dashboard.images.form', compact('repositoryImage', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $repositoryImage = new Image;
        $text = $this->message;

        return view('dashboard.images.form', compact('repositoryImage', 'text'));
    }
}
