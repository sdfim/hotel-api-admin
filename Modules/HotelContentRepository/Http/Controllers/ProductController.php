<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\Product;

class ProductController extends BaseWithPolicyController
{
    protected static string $model = Product::class;

    protected static ?string $parameterName = 'product-repository';

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
        $product = new Product;
        $text = $this->message;

        return view('dashboard.hotel_repository.products.form', compact('product', 'text'));
    }
}
