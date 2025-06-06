<?php

namespace App\Models;

use Database\Factories\DepositInformationConditionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositInformationCondition extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return DepositInformationConditionFactory::new();
    }

    protected $table = 'deposit_information_conditions';

    protected $fillable = [
        'deposit_information_id',
        'field',
        'compare',
        'value',
        'value_from',
        'value_to',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public function depositInformation(): BelongsTo
    {
        return $this->belongsTo(DepositInformation::class, 'deposit_information_id', 'id');
    }
}
