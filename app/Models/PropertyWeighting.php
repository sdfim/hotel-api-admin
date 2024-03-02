<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyWeighting extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'property',
        'supplier_id',
        'weight',
        'created_at',
        'updated_at'
    ];

    /**
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * @return BelongsTo
     */
    public function giataProperties(): BelongsTo
    {
        return $this->belongsTo(GiataProperty::class, 'property', 'code');
    }
}
