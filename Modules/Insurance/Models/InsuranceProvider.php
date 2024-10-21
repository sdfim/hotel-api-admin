<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceProvider extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact_info'];

    public function plans(): HasMany
    {
        return $this->hasMany(InsurancePlan::class);
    }

    public function restrictions(): HasMany
    {
        return $this->hasMany(InsuranceRestriction::class, 'provider_id');
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(InsuranceProviderDocumentation::class);
    }
}
