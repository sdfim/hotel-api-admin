<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductDescriptiveContent;

class ProductDescriptiveContentDTO
{
    public $id;

    public $content_sections_id;

    public $descriptive_type_id;

    public $value;

    public function __construct() {}

    public function transform(Collection $productDescriptiveContents)
    {
        return $productDescriptiveContents->map(function ($productDescriptiveContent) {
            return $this->transformContent($productDescriptiveContent);
        })->all();
    }

    public function transformContent(ProductDescriptiveContent $productDescriptiveContent)
    {
        return [
            'id' => $productDescriptiveContent->id,
            'content_sections_id' => $productDescriptiveContent->content_sections_id,
            'descriptive_type_id' => $productDescriptiveContent->descriptive_type_id,
            'value' => $productDescriptiveContent->value,
        ];
    }
}
