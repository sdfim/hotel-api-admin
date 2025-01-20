<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigJobDescription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\HotelContentRepository\Models\Factories\ContactInformationFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ContactInformation extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ContactInformationFactory::new();
    }

    protected $table = 'pd_contact_information';

    protected $fillable = [
        'first_name',
        'last_name',
        'job_title',
        'contactable_id',
        'contactable_type',
        'ujv_department'
    ];

    public function contactable()
    {
        return $this->morphTo();
    }

    public function emails()
    {
        return $this->hasMany(ContactInformationEmails::class);
    }

    public function phones()
    {
        return $this->hasMany(ContactInformationPhones::class);
    }

    public function ujvDepartments(): BelongsToMany
    {
        return $this->belongsToMany(ConfigJobDescription::class, 'pd_contact_information_job_descriptions', 'contact_information_id', 'job_descriptions_id');
    }
}
