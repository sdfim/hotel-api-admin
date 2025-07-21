<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\ImageGallery;

class ImageGalleryController extends BaseWithPolicyController
{
    protected static string $model = ImageGallery::class;

    private array $message = ['edit' => 'Edit Image Gallery', 'create' => 'Create Image Gallery'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.image-galleries.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $imageGallery = ImageGallery::findOrFail($id);
        $text = $this->message;

        return view('dashboard.image-galleries.form', compact('imageGallery', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $imageGallery = new ImageGallery;
        $text = $this->message;

        return view('dashboard.image-galleries.form', compact('imageGallery', 'text'));
    }
}
