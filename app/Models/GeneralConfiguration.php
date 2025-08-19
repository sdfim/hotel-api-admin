<?php

namespace App\Models;

use App\Observers\GeneralConfigurationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([GeneralConfigurationObserver::class])]
class GeneralConfiguration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'time_supplier_requests',
        'time_reservations_kept',
        'currently_suppliers',
        'time_inspector_retained',
        'star_ratings',
        'stop_bookings',
        'content_supplier',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'time_supplier_requests' => 'integer',
        'time_reservations_kept' => 'integer',
        'currently_suppliers' => 'array',
        'time_inspector_retained' => 'integer',
        'star_ratings' => 'float',
        'stop_bookings' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
