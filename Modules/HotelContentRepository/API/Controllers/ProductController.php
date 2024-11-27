<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Actions\Product\AddProduct;
use Modules\HotelContentRepository\Actions\Product\DeleteProduct;
use Modules\HotelContentRepository\Actions\Product\EditProduct;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\API\Requests\ProductRequest;
use Modules\HotelContentRepository\Models\DTOs\ProductDTO;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\Transformers\ProductTransformer;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;
use Spatie\Fractal\Fractal;

class ProductController extends BaseController
{
    public function __construct(
        protected AddProduct $addProduct,
        protected EditProduct $editProduct,
        protected DeleteProduct $deleteProduct,
        protected ProductDTO $productDTO
    ) {}

    public function index()
    {
        $query = Product::query();
        $query = $this->filter($query, Product::class);
        $products = $query->with($this->getIncludes())->get();

        $useFractal = env('USE_FRACTAL', true);
        if (!$useFractal) {
            $productDTO = $this->productDTO->transform($products, true);
        } else {
            $productDTO = Fractal::create()
                ->collection($products)
                ->transformWith(new ProductTransformer())
                ->toArray()['data'];
        }

        return $this->sendResponse($productDTO, 'index success');
    }

    public function store(ProductRequest $request)
    {
        $product = $this->addProduct->handle($request);
        return $this->sendResponse($product->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $product = Product::with($this->getIncludes())->findOrFail($id);

        $useFractal = env('USE_FRACTAL', true);
        if (!$useFractal) {
            $productDTO = $this->productDTO->transform($product->get(), true);
        } else {
            $productDTO = Fractal::create()
                ->item($product)
                ->transformWith(new ProductTransformer())
                ->toArray()['data'];
        }

        return $this->sendResponse([$productDTO], 'show success');
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product = $this->editProduct->handle($product, $request);
        return $this->sendResponse($product->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $this->deleteProduct->handle($product);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }

    public function attachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->galleries()->attach($request->gallery_id);
        return $this->sendResponse($product->galleries->toArray(), 'Gallery attached successfully');
    }

    public function detachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->galleries()->detach($request->gallery_id);
        return $this->sendResponse($product->galleries->toArray(), 'Gallery detached successfully');
    }

    protected function getIncludes(): array
    {
        return [
            'affiliations',
            'attributes',
            'contentSource',
            'propertyImagesSource',
            'descriptiveContentsSection.content',
            'feeTaxes',
            'informativeServices.service',
            'promotions.galleries.images',
            'keyMappings',
            'galleries.images',
            'contactInformation',
        ];
    }
}
