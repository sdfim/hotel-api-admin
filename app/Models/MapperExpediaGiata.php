<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MapperExpediaGiata extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'expedia_id',
        'giata_id',
        'step',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var mixed
     */
    protected $connection;

    /**
     * @var string[]
     */
    protected $primaryKey = ['expedia_id', 'giata_id'];

    protected $table = 'mapper_expedia_giatas';

    /**
     * @var bool
     */
    public $incrementing = false;

    public function expedia(): HasOne
    {
        return $this->hasOne(ExpediaContent::class, 'property_id', 'expedia_id');
    }
}
