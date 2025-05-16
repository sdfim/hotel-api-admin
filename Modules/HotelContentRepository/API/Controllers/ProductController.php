<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use Modules\HotelContentRepository\Actions\Product\AddProduct;
use Modules\HotelContentRepository\Actions\Product\DeleteProduct;
use Modules\HotelContentRepository\Actions\Product\EditProduct;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\API\Requests\ProductRequest;
use Modules\HotelContentRepository\Models\DTOs\ProductDTO;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\Transformers\CustomFractalSerializer;
use Modules\HotelContentRepository\Models\Transformers\ProductTransformer;

class ProductController extends BaseController
{
    protected Manager $fractal;

    public function __construct(
        protected AddProduct $addProduct,
        protected EditProduct $editProduct,
        protected DeleteProduct $deleteProduct,
        protected ProductDTO $productDTO
    ) {
        $this->fractal = app(Manager::class);
        $this->fractal->setSerializer(app(CustomFractalSerializer::class));
    }

    public function index()
    {
        $query = Product::query();
        $query = $this->filter($query, Product::class);
        $products = $query->get();

        $useFractal = config('packages.fractal.use_fractal', true);
        if (! $useFractal) {
            $productDTO = $this->productDTO->transform($products, true);
        } else {
            $resource = new FractalCollection($products, new ProductTransformer());
            $productDTO = $this->fractal->createData($resource)->toArray();
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
        try {
            $product = Product::findOrFail($id);
            \Log::debug('show', [$product->contactInformation]);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Product not found', Response::HTTP_NOT_FOUND);
        }

        $useFractal = config('packages.fractal.use_fractal', true);
        if (! $useFractal) {
            $productDTO = $this->productDTO->transformProduct($product, true);
        } else {
            $resource = new FractalCollection([$product], new ProductTransformer());
            $productDTO = $this->fractal->createData($resource)->toArray()[0];
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
}
