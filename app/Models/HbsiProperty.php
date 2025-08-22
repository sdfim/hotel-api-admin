<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HbsiProperty extends Model
{
    protected $table = 'hbsi_properties';

    protected $fillable = [
        'hotel_code',
        'hotel_name',
        'city_code',
        'address_line',
        'city_name',
        'state',
        'postal_code',
        'country_name',
        'phone',
        'emails',
        'rateplans',
        'roomtypes',
        'tpa_extensions',
        'raw_xml',
    ];

    protected $casts = [
        'rateplans' => 'array',
        'roomtypes' => 'array',
        'tpa_extensions' => 'array',
        'emails' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    public function getHasRoomTypesAttribute(): bool
    {
        return ! empty($this->roomtypes);
    }

    public function getHasRatePlansAttribute(): bool
    {
        return ! empty($this->rateplans);
    }

    public function mapperHbsiGiata(): HasMany
    {
        return $this->hasMany(Mapping::class, 'supplier_id', 'hotel_code')->hbsi();
    }
}
