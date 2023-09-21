<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservations extends Model
{
    use HasFactory;

    protected $fillable = ['date_offload','date_travel','passenger_surname','contains_id','channel_id','total_cost','canceled_at','created_at','updated_at'];

    public function channel(){
        return $this->belongsTo(Channels::class);
    }

    public function contains(){
        return $this->belongsTo(Contains::class);
    }
}
