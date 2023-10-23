<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
		'date_offload',
		'date_travel',
		'passenger_surname',
		'reservation_contains',
		'channel_id',
		'total_cost',
		'canceled_at',
		'created_at',
		'updated_at'
	];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function contains(): BelongsTo
    {
        return $this->belongsTo(Contains::class);
    }
}
