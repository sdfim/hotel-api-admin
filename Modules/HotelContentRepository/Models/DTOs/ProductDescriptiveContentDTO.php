<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductDescriptiveContent;

class ProductDescriptiveContentDTO
{
    public $id;
    public $content_sections_id;
    public $descriptive_type_id;
    public $value;

    public function __construct(ProductDescriptiveContent $content)
    {
        $this->id = $content->id;
        $this->content_sections_id = $content->content_sections_id;
        $this->descriptive_type_id = $content->descriptive_type_id;
        $this->value = $content->value;
    }
}
