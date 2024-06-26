<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiataPlace extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'parent_key',
        'name_primary',
        'type',
        'state',
        'country_code',
        'name_others',
        'tticodes',
    ];

    protected function casts(): array
    {
        return [
            'airports' => 'array',
            'name_others' => 'array',
            'tticodes' => 'array',
        ];
    }
}
