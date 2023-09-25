<?php
namespace Modules\API\ContentAPI\Controllers;

class HotelSearchBuilder
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function applyFilters(array $filters)
    {
        if (isset($filters['destination'])) {
            $this->query->where('city', '=', $filters['destination']);
        }

        if (isset($filters['rating'])) {
            $this->query->where('rating', '>=', $filters['rating']);
        }

        for ($i = 1; $i <= 3; $i++) {
            $roomKey = "room{$i}";
			$total = 0;
            if (isset($filters[$roomKey])) {
				$arr = explode('-', $filters[$roomKey]);
				$total_current = $arr[0] + $arr[1];
				if ($total < $total_current) {
					$total = $total_current;
				}
            }
			$this->query->where('total_occupancy', '>=', $total);
        }

        return $this->query;
    }
}
