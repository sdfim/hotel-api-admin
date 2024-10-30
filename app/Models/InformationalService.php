<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformationalService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cost',
        'date',
        'time',
        'type',
    ];
}
