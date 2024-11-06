<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelAffiliationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class HotelAffiliation extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return HotelAffiliationFactory::new();
    }

    protected $table = 'pd_hotel_affiliations';

    protected $fillable = [
        'hotel_id',
        'affiliation_name',
        'combinable',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
