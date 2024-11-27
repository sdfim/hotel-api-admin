<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigConsortium;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductAffiliationDetailFactory;

class ProductAffiliationDetail extends Model
{
    use HasFactory;
    protected static function newFactory()
    {
        return ProductAffiliationDetailFactory::new();
    }

    protected $table = 'pd_product_affiliation_details';

    protected $fillable = [
        'affiliation_id',
        'consortia_id',
        'description',
        'start_date',
        'end_date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(ProductAffiliation::class, 'affiliation_id');
    }

    public function consortia(): BelongsTo
    {
        return $this->belongsTo(ConfigConsortium::class, 'consortia_id');
    }
}
