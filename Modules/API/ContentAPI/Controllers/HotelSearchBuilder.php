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
 
 		// TODO: [UJV-4] add ocuppancy filter
 		if (isset($filters['ocuppancy'])) {
 			$max_ocuppancy = 1;
 			foreach ($filters['ocuppancy'] as $value) {
 				$current_ocuppancy = $value['adults'] + ($value['children'] ?? 0);
 				if ($current_ocuppancy > $max_ocuppancy) {
 					$max_ocuppancy = $current_ocuppancy;
 				}
 			}
             $this->query->where('total_occupancy', '>=', $max_ocuppancy);
         }

        return $this->query;
    }
}
