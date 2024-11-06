<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InsuranceApplication
 *
 * @property int $id
 * @property int $insurance_plan_id
 * @property string $room_number
 * @property string $name
 * @property string $location
 * @property int $age
 * @property float $total_insurance_cost_pp
 *
 * @property InsurancePlan $plan
 */
class InsuranceApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_plan_id',
        'room_number',
        'name',
        'location',
        'age',
        'total_insurance_cost_pp',
        'created_at',
        'updated_at',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class, 'insurance_plan_id');
    }
}
