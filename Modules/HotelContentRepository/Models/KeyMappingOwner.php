<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\KeyMappingOwnerFactory;

class KeyMappingOwner extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return KeyMappingOwnerFactory::new();
    }

    protected $table = 'pd_key_mapping_owners';

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
