<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'description'];

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRules::class);
    }

    public static function getExpediaId()
    {
        return Supplier::where('name', 'Expedia')->first()->id;
    }

}
