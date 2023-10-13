<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MapperExpediaGiata extends Model
{
    use HasFactory;

    protected $fillable = [
        'expedia_id',
        'giata_id',
        'step',
    ];
    public $timestamps = false;
    protected $connection;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
    }

    public function expedia(): HasOne
    {
        return $this->hasOne(ExpediaContent::class, 'property_id', 'expedia_id');
    }
}
