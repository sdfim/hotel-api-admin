<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigJobDescription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\HotelContentRepository\Models\Factories\ProductContactInformationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductContactInformation extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductContactInformationFactory::new();
    }

    protected $table = 'pd_product_contact_information';

    protected $fillable = [
        'product_id',
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function contactInformations(): BelongsToMany
    {
        return $this->belongsToMany(ConfigJobDescription::class, 'pd_product_contact_information_job_descriptions', 'contact_information_id', 'job_descriptions_id');
    }
}
