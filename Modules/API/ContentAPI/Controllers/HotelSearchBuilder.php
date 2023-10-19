<?php

namespace Modules\API\ContentAPI\Controllers;

class HotelSearchBuilder
{
    protected $query;

    public function __construct ($query)
    {
        $this->query = $query;
    }

    public function applyFilters (array $filters)
    {
        // if (isset($filters['destination'])) {
        //     $this->query->where('city', '=', $filters['destination']);
        // }

		if (isset($filters['ids'])) {
			$this->query->whereIn('property_id', $filters['ids']);
		}

        if (isset($filters['rating'])) {
            $this->query->where('rating', '>=', $filters['rating']);
        }
 
 		// TODO: [UJV-4] add occupancy filter
 		if (isset($filters['occupancy'])) {
 			$max_occupancy = 1;
 			foreach ($filters['occupancy'] as $value) {
 				$current_occupancy = $value['adults'] + ($value['children'] ?? 0);
 				if ($current_occupancy > $max_occupancy) {
 					$max_occupancy = $current_occupancy;
 				}
 			}
             $this->query->where('total_occupancy', '>=', $max_occupancy);
         }

        return $this->query;
    }
}
