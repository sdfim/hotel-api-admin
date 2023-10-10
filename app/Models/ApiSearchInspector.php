<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

class ApiSearchInspector extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];

    protected $fillable = [
        'id',
        'token_id',
        'suppliers',
        'type',
        'request',
        'response_path',
		'client_response_path'
    ];

    protected static function booted (): void
    {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::uuid()->toString();
        });
    }


    public function token ()
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

	public function getLinckPriceCheck ($filters) : string
	{
		$uuid = $filters['inspector'];
		$hotel_id = $filters['hotel_id']; // giata_id
		$room_id = $filters['room_id']; // expedia
		$rate_id = $filters['rate'] ?? ''; // expedia
		$bed_groups = $filters['bed_groups'] ?? ''; // expedia

		$inspector = ApiSearchInspector::where('id', $uuid)->first();
		$json_response = json_decode(Storage::get($inspector->response_path));
		$rooms = $json_response->results->Expedia->$hotel_id->rooms;

		$linkPriceCheck = '';
		foreach ($rooms as $room) {
			if ($room->id == $room_id) {
				$rates = $room->rates;
				foreach ($rates as $rate) {
					if ($rate->id == $rate_id) {
						$linkPriceCheck = $rate->bed_groups->$bed_groups->links->price_check->href;
					}
				}
				break;
			}
		}

		return $linkPriceCheck;
	}
}
