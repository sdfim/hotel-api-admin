<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpediaContent extends Model
{
    use HasFactory;

    protected $connection;

    protected $casts = [
        'address' => 'array',
        'ratings' => 'array',
        'location' => 'array',
        'category' => 'array',
        'business_model' => 'array',
        'checkin' => 'array',
        'checkout' => 'array',
        'fees' => 'array',
        'policies' => 'array',
        'attributes' => 'array',
        'amenities' => 'array',
        'images' => 'array',
        'onsite_payments' => 'array',
        'rooms' => 'array',
        'rates' => 'array',
        'dates' => 'array',
        'descriptions' => 'array',
        'themes' => 'array',
        'chain' => 'array',
        'brand' => 'array',
        'statistics' => 'array',
        'vacation_rental_details' => 'array',
        'airports' => 'array',
        'spoken_languages' => 'array',
        'all_inclusive' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api'). '.' .'giata_properties';
    }

    public function getFullListFields(): array
    {
        return [
            'property_id', 'name', 'address', 'ratings', 'location',
            'category', 'business_model', 'checkin', 'checkout',
            'fees', 'policies', 'attributes', 'amenities',
            'onsite_payments', 'rates',
            'images', 'rooms',
            'dates', 'descriptions', 'themes', 'chain', 'brand',
            'statistics', 'vacation_rental_details', 'airports',
            'spoken_languages', 'all_inclusive', 'rooms_occupancy',
            'total_occupancy', 'city', 'rating'
        ];
    }

    public function getShortListFields(): array
    {
        return [
            'property_id', 'name', 'address', 'ratings', 'location',
            'category', 'business_model',
            'fees', 'policies', 'attributes', 'amenities',
            'onsite_payments',
            // 'rates',
            'statistics', 'vacation_rental_details', 'airports',
            'total_occupancy', 'city', 'rating', 'rooms_occupancy',
        ];
    }

    public function dtoDbToResponse($results, $fields)
    {
        return collect($results)->map(function ($item) use ($fields) {
            foreach ($fields as $key) {
                if (!is_string($item->$key)) continue;
                if (str_contains($item->$key, '{')) {
                    $item->$key = json_decode($item->$key);
                }
            }
            return $item;
        });
    }

    public function mapperGiataExpedia(): HasMany
    {
        return $this->hasMany(MapperExpediaGiata::class, 'expedia_id', 'property_id');
    }

    public function getIdsByDestinationGiata($city): array
    {
        $ids = GiataProperty::where('city', $city)
            ->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
            ->select('mapper_expedia_giatas.expedia_id')
            ->whereNotNull('mapper_expedia_giatas.expedia_id')
            ->get()
            ->pluck('expedia_id')
            ->toArray();

        return $ids;
    }
}
