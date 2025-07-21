<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Factories\ContactInformationPhonesFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ContactInformationPhones extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ContactInformationPhonesFactory::new();
    }

    protected $table = 'pd_contact_information_phones';

    protected $fillable = [
        'contact_information_id',
        'country_code',
        'area_code',
        'phone',
        'extension',
        'description',
    ];

    public function information(): BelongsTo
    {
        return $this->belongsTo(ContactInformation::class, 'contact_information_id');
    }
}
