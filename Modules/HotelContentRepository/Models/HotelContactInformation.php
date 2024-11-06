<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigJobDescription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelContactInformationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class HotelContactInformation extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return HotelContactInformationFactory::new();
    }

    protected $table = 'pd_hotel_contact_information';

    protected $fillable = [
        'hotel_id',
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function contactInformations()
    {
        return $this->belongsToMany(ConfigJobDescription::class, 'pd_hotel_contact_information_job_descriptions', 'contact_information_id', 'job_descriptions_id');
    }
}
