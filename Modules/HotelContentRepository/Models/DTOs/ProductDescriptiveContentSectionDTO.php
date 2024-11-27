<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentSectionDTO
{
    public $id;
    public $product_id;
    public $section_name;
    public $start_date;
    public $end_date;
    public $content;

    public function __construct(ProductDescriptiveContentSection $section)
    {
        $this->id = $section->id;
        $this->product_id = $section->product_id;
        $this->section_name = $section->section_name;
        $this->start_date = $section->start_date;
        $this->end_date = $section->end_date;
        $this->content = $section->content->map(function ($content) {
            return new ProductDescriptiveContentDTO($content);
        });
    }
}
