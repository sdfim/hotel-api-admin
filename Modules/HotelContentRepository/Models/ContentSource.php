<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\ContentSourceFactory;

class ContentSource extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ContentSourceFactory::new();
    }

    protected $table = 'pd_content_sources';

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
