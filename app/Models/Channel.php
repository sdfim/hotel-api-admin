<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\Sanctum;
use Modules\HotelContentRepository\Models\Product;

class Channel extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'token_id',
        'access_token',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Sanctum::$personalAccessTokenModel);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'pd_product_channel');
    }
}
