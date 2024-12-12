<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\ProductDescriptiveContentSectionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductDescriptiveContentSection extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductDescriptiveContentSectionFactory::new();
    }

    protected $table = 'pd_product_descriptive_content_sections';

    protected $fillable = [
        'product_id',
        'section_name',
        'start_date',
        'end_date',
        'descriptive_type_id',
        'value',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function descriptiveType()
    {
        return $this->belongsTo(ConfigDescriptiveType::class, 'descriptive_type_id');
    }
}
