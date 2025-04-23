<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigJobDescription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\HotelContentRepository\Models\Factories\ContactInformationEmailsFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ContactInformationEmails extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ContactInformationEmailsFactory::new();
    }

    protected $table = 'pd_contact_information_emails';

    protected $fillable = [
        'contact_information_id',
        'email',
        'departments',
    ];

    protected $casts = [
        'departments' => 'array',
    ];

    public function information(): BelongsTo
    {
        return $this->belongsTo(ContactInformation::class, 'contact_information_id');
    }
}
