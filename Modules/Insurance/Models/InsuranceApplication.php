<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_plan_id',
        'room_number',
        'name',
        'location',
        'age',
        'applied_at',
        'total_insurance_cost_pp',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class, 'insurance_plan_id');
    }
}
