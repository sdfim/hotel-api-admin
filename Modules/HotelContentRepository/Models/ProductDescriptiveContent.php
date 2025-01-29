<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\ProductDescriptiveContentFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductDescriptiveContent extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ProductDescriptiveContentFactory::new();
    }

    protected $table = 'pd_product_descriptive_content';

    protected $fillable = [
        'content_sections_id',
        'descriptive_type_id',
        'value',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function contentSection()
    {
        return $this->belongsTo(ProductDescriptiveContentSection::class, 'content_sections_id');
    }

    public function descriptiveType()
    {
        return $this->belongsTo(ConfigDescriptiveType::class, 'descriptive_type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['content_sections_id', 'descriptive_type_id', 'value'])
            ->logOnlyDirty()
            ->useLogName('product_descriptive_content');
    }
}
