<?php

namespace Modules\Insurance\Models;

use Carbon\Carbon;
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
 * @property Carbon $applied_at
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
        'applied_at',
        'total_insurance_cost_pp',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class, 'insurance_plan_id');
    }
}
