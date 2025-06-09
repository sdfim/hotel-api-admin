<?php

namespace App\Models;

use Database\Factories\DepositInformationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DepositInformation extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return DepositInformationFactory::new();
    }

    protected $table = 'deposit_information';

    protected $fillable = [
        'giata_code',
        'rate_id',
        'name',
        'start_date',
        'expiration_date',
        'manipulable_price_type',
        'price_value',
        'price_value_type',
        'price_value_target',
        'days_after_booking_initial_payment_due',
        'days_before_arrival_initial_payment_due',
        'date_initial_payment_due',
        'days_initial_payment_due',
        'initial_payment_due_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiration_date' => 'date',
        'date_initial_payment_due' => 'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function giata(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'giata_code', 'code');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(DepositInformationCondition::class, 'deposit_information_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('deposit_information');
    }
}
