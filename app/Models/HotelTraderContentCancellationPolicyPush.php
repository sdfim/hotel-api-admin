<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTraderContentCancellationPolicyPush extends Model
{
    use HasFactory;

    protected $table = 'hotel_trader_content_cancellation_policies';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $fillable = [
        'hotel_code',
        'code',
        'name',
        'description',
        'penalty_windows', // JSON
    ];

    protected $casts = [
        'penalty_windows' => 'array',
    ];
}

