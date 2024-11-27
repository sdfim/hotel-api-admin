<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Modules\HotelContentRepository\Models\Product;
use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;

class ProductController extends Controller
{
    protected static string $model = Product::class;
    protected static ?string $parameterName = 'product';

    private array $message = ['edit' => 'Edit Product', 'create' => 'Create Product'];

    public function index(): View
    {
        return view('dashboard.hotel_repository.products.index');
    }

    public function show(string $id): View
    {
        $product = Product::findOrFail($id);
        return view('dashboard.hotel_repository.products.show', compact('product'));
    }

    public function edit(string $id): View
    {
        $product = Product::findOrFail($id);
        $text = $this->message;

        return view('dashboard.hotel_repository.products.form', compact('product', 'text'));
    }

    public function create(): View
    {
        $product = new Product();
        $text = $this->message;
        return view('dashboard.hotel_repository.products.form', compact('product', 'text'));
    }
}
