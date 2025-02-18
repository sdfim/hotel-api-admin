<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyLocation extends Model
{
    protected $table = 'property_locations';

    protected $connection = 'mysql_cache';

    protected $primaryKey = 'property_code';

    public $timestamps = false;

    protected $fillable = [
        'property_code',
        'location',
    ];

    protected $spatialFields = [
        'location',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'code');
    }
}
