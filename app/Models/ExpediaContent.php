<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class ExpediaContent extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $connection;
	protected $primaryKey = 'property_id';
	# protected const TABLE = 'expedia_contents';
	protected const TABLE = 'expedia_content_main';

	/**
     * @var string[]
     */
    protected $fillable = [
        'property_id', 
		'name',
		'address', 
		'ratings', 
		'location',
		'latitude',
		'longitude',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'address' => 'array',
        'ratings' => 'array',
        'location' => 'array',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = env(('DB_CONNECTION_2'), 'mysql2');
        $this->table = env(('SECOND_DB_DATABASE'), 'ujv_api') . '.' . self::TABLE;
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
            'property_id', 'name', 'images', 'location', 'amenities', 'rating',
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

	public function expediaSlave(): HasOne
    {
        return $this->hasOne(ExpediaContentSlave::class, 'expedia_property_id', 'property_id');
    }

    /**
     * @param $city
     * @return array
     */
    public function getIdsByDestinationGiata(string $input): array
    {
		if ( is_numeric($input))  $query = GiataProperty::where('city_id', $input);
		else $query = GiataProperty::where('city', $input);
		
        return $query->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
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
        $expedia = ExpediaContent::leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.expedia_id', '=', self::TABLE . '.property_id')
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
        return ExpediaContent::where('property_id', $hotel_id)
            ->select('name')
            ->first()
			->name;
    }

    /**
     * @param int $hotel_id
     * @return array
     */
    public function getHotelImagesByHotelId(int $hotel_id): array
    {
        $expedia = ExpediaContent::where('property_id', $hotel_id)
			->leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
            ->select('expedia_content_slave.images as images')
            ->first();

        $images = [];
        $countImages = 0;
        foreach (json_decode($expedia->images, true) as $image) {
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
