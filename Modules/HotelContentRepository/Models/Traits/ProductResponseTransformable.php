<?php

namespace Modules\HotelContentRepository\Models\Traits;

trait ProductResponseTransformable
{
    public function filterAndTransformProductResponse($query)
    {
        $giataCode = request()->get('giata_code') ?: null;
        $productName = request()->get('product_name') ?: null;

        if ($giataCode) {
            $query->whereHas('product.related', function ($query) use ($giataCode) {
                $query->where('giata_code', $giataCode);
            });
        }

        if ($productName) {
            $query->whereHas('product', function ($query) use ($productName) {
                $query->where('name', 'like', '%'.$productName.'%');
            });
        }

        $hotelInformativeServices = $query->get();

        if ($giataCode || $productName) {
            $res = $hotelInformativeServices->map(function ($service) {
                $serviceArray = $service->toArray();
                unset($serviceArray['product']);
                $giataCodeRS = optional($service->product->related)->giata_code;
                $productNameRS = optional($service->product)->name;

                return array_merge($serviceArray, [
                    'giata_code' => $giataCodeRS,
                    'product_name' => $productNameRS,
                ]);
            });
        } else {
            $res = $hotelInformativeServices;
        }

        return $res;
    }
}
