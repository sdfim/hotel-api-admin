<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductAffiliationDetail;

class ProductAffiliationDetailDTO
{
    public $affiliation_id;
    public $consortia_id;
    public $description;
    public $start_date;
    public $end_date;
    public $combinable;

    public function __construct() {}

    public function transform(Collection $details)
    {
        return $details->map(function ($detail) {
            return $this->transformDetail($detail);
        })->all();
    }

    public function transformDetail(ProductAffiliationDetail $detail)
    {
        return [
            'affiliation_id' => $detail->affiliation_id,
            'consortia' => $detail->consortia->name,
            'description' => $detail->description,
            'start_date' => $detail->start_date,
            'end_date' => $detail->end_date,
            'combinable' => $detail->combinable,
        ];
    }
}
