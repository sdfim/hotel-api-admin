<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weights extends Model
{
    use HasFactory;

    protected $table = 'weights';

    protected $fillable = ['property', 'supplier_id', 'weight', 'created_at', 'updated_at'];

    public function suppliers(): BelongsTo
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }

    public function giataProperties(): BelongsTo
    {
        return $this->belongsTo(GiataProperty::class, 'property', 'code');
    }

}
