<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weights extends Model
{
    use HasFactory;

    protected $fillable = ['property', 'supplier_id', 'weight', 'created_at', 'updated_at'];

    public function supplier ()
    {
        return $this->belongsTo(Suppliers::class);
    }

}
