<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiataProperty extends Model
{
    use HasFactory;
	protected $connection = env(('DB_CONNECTION_2'), 'mysql2');
	protected $table = 'giata_properties';

    protected $fillable = [
        'code',
        'last_updated',
        'name',
        'chain',
        'city',
        'locale',
        'address',
        'phone',
        'position',
        'url',
        'cross_references',
    ];

    protected $casts = [
        'chain' => 'json',
        'address' => 'json',
        'phone' => 'json',
        'position' => 'json',
        'cross_references' => 'json',
    ];
}
