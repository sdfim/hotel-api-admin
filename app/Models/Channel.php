<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\Sanctum;
use Modules\HotelContentRepository\Models\Product;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Channel extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'token_id',
        'access_token',
        'user_id',
        'accept_special_params'
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Sanctum::$personalAccessTokenModel);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'pd_product_channel');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function delete()
    {
        $this->name .= '_deleted_'.now()->timestamp;
        $this->save();

        return parent::delete();
    }

    public function findForToken($token)
    {
        return $this->where('access_token', $token)->first();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('channel');
    }
}
