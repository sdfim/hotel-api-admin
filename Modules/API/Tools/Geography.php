<?php

namespace Modules\API\Tools;

class Geography 
{
	/**
	 * @param float $latitude
	 * @param float $longitude
	 * @param float $radius
	 * @return array
	 */
	function calculateBoundingBox(float $latitude, float $longitude, float $radius) : array
	{
		$earthRadius = 6371; // Радиус Земли в километрах
	
		// Переводим радиус из километров в радианы
		$radiusInRadians = $radius / $earthRadius;
	
		// Переводим широту и долготу в радианы
		$latitude = deg2rad($latitude);
		$longitude = deg2rad($longitude);
	
		// Вычисляем минимальную и максимальную широту
		$minLatitude = $latitude - $radiusInRadians;
		$maxLatitude = $latitude + $radiusInRadians;
	
		// Вычисляем минимальную и максимальную долготу
		$minLongitude = $longitude - $radiusInRadians;
		$maxLongitude = $longitude + $radiusInRadians;
	
		// Переводим результаты обратно из радиан в градусы
		$minLatitude = rad2deg($minLatitude);
		$maxLatitude = rad2deg($maxLatitude);
		$minLongitude = rad2deg($minLongitude);
		$maxLongitude = rad2deg($maxLongitude);
	
		return [
			'min_latitude' => $minLatitude,
			'max_latitude' => $maxLatitude,
			'min_longitude' => $minLongitude,
			'max_longitude' => $maxLongitude
		];
	}
	
}

// // Пример использования
// $centerLatitude = 40.7128; // Центральная широта (например, Нью-Йорк)
// $centerLongitude = -74.0060; // Центральная долгота
// $searchRadius = 10; // Радиус поиска в километрах

// $boundingBox = $this->calculateBoundingBox($centerLatitude, $centerLongitude, $searchRadius);

// // Вывод результатов
// echo "Минимальная широта: " . $boundingBox['min_latitude'] . "<br>";
// echo "Максимальная широта: " . $boundingBox['max_latitude'] . "<br>";
// echo "Минимальная долгота: " . $boundingBox['min_longitude'] . "<br>";
// echo "Максимальная долгота: " . $boundingBox['max_longitude'] . "<br>";


