<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiExceptionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
		'report_id',
        'level',
		'supplier_id',
		'action',
		'description',
        'response_path'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
