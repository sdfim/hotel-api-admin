<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentSectionDTO
{
    public $id;
    public $product_id;
    public $section_name;
    public $start_date;
    public $end_date;
    public $content;

    public function __construct() {}

    public function transform(Collection $sections)
    {
        return $sections->map(function ($section) {
            return $this->transformSection($section);
        })->all();
    }

    public function transformSection(ProductDescriptiveContentSection $section)
    {
        return [
            'id' => $section->id,
            'product_id' => $section->product_id,
            'section_name' => $section->section_name,
            'start_date' => $section->start_date,
            'end_date' => $section->end_date,
            'content' => $section->content->map(function ($content) {
                return (new ProductDescriptiveContentDTO())->transformContent($content);
            }),
        ];
    }
}
