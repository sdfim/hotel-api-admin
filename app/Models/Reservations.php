<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservations extends Model
{
    use HasFactory;

    public function channel(){
        return $this->belongsTo(Channels::class);
    }

    public function contains(){
        return $this->belongsTo(Contains::class);
    }
}
