<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ExpediaContent extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $connection;
	protected $primaryKey = 'property_id';

    /**
     * @var string[]
     */
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

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . 'expedia_contents';
    }

    /**
     * @return string[]
     */
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

    /**
     * @return string[]
     */
    public function getShortListFields(): array
    {
        return [
            'property_id', 'name', 'address', 'ratings', 'location',
            'category', 'business_model',
            'fees', 'policies', 'attributes', 'amenities',
            'onsite_payments', 'images',
            'statistics', 'vacation_rental_details', 'airports',
            'total_occupancy', 'city', 'rating', 'rooms_occupancy',
        ];
    }

    /**
     * @param $results
     * @param $fields
     * @return Collection
     */
    public function dtoDbToResponse($results, $fields): Collection
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

    /**
     * @return HasMany
     */
    public function mapperGiataExpedia(): HasMany
    {
        return $this->hasMany(MapperExpediaGiata::class, 'expedia_id', 'property_id');
    }

    /**
     * @param $city
     * @return array
     */
    public function getIdsByDestinationGiata($city): array
    {
        return GiataProperty::where('city', $city)
            ->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
            ->select('mapper_expedia_giatas.expedia_id')
            ->whereNotNull('mapper_expedia_giatas.expedia_id')
            ->get()
            ->pluck('expedia_id')
            ->toArray();
    }

    /**
     * @param $giata_id
     * @return int
     */
    public function getExpediaIdByGiataId($giata_id): int
    {
        $expedia = ExpediaContent::leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.expedia_id', '=', 'expedia_contents.property_id')
            ->leftJoin('giata_properties', 'mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
            ->select('mapper_expedia_giatas.expedia_id')
            ->where('mapper_expedia_giatas.giata_id', $giata_id)
            ->get()
            ->first();

        return $expedia->expedia_id;
    }

    /**
     * @param int $hotel_id
     * @return string
     */
    public function getHotelNameByHotelId(int $hotel_id): string
    {
        $expedia = ExpediaContent::where('property_id', $hotel_id)
            ->select('name')
            ->get()
            ->first();

        return $expedia->name;
    }

    /**
     * @param int $hotel_id
     * @return array
     */
    public function getHotelImagesByHotelId(int $hotel_id): array
    {
        $expedia = ExpediaContent::where('property_id', $hotel_id)
            ->select('images')
            ->get()
            ->first();

        $images = [];
        $countImages = 0;
        foreach ($expedia->images as $image) {
            if ($countImages == 5) break;
            $images[] = $image['links']['350px']['href'];
            $countImages++;
        }

        return $images;
    }

	/**
	 * @param $minMaxCoordinate
	 * @return array
	 */
	public function getIdsByCoordinate(array $minMaxCoordinate): array
	{
	
		return GiataProperty::where('giata_properties.latitude', '>', $minMaxCoordinate['min_latitude'])
			->where('giata_properties.latitude', '<', $minMaxCoordinate['max_latitude'])
			->where('giata_properties.longitude', '>', $minMaxCoordinate['min_longitude'])
			->where('giata_properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
            ->select('mapper_expedia_giatas.expedia_id')
            ->whereNotNull('mapper_expedia_giatas.expedia_id')
            ->pluck('expedia_id')
            ->toArray();
	}
}
