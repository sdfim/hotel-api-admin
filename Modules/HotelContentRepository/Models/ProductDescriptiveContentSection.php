<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ProductDescriptiveContentSectionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductDescriptiveContentSection extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ProductDescriptiveContentSectionFactory::new();
    }

    protected $table = 'pd_product_descriptive_content_sections';

    protected $fillable = [
        'product_id',
        'rate_id',
        'section_name',
        'start_date',
        'end_date',
        'descriptive_type_id',
        'value',
        'document_description',
        'document_path',
        'priority_rooms',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'priority_rooms' => 'array',
        ];
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(HotelRate::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function descriptiveType(): BelongsTo
    {
        return $this->belongsTo(ConfigDescriptiveType::class, 'descriptive_type_id');
    }

    public function priorityRooms()
    {
        return HotelRoom::whereIn('id', $this->priority_rooms);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('product_descriptive_content_section');
    }
}
