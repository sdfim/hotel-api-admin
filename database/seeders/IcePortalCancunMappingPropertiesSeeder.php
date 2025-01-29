<?php

namespace Database\Seeders;

use App\Models\Mapping;
use Illuminate\Database\Seeder;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class IcePortalCancunMappingPropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->hbsi();
    }

    public function hbsi()
    {

        //Create Test Properties Mappings
        $mappingsData = $this->getData();

        foreach($mappingsData as $mappingData)
        {
            $mapping = Mapping::firstOrNew($mappingData);
            $mapping->save();
        }
    }

    private function getData(): array
    {
        return [
            [
                "giata_id" => 10057691,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "147091",
                "match_percentage" => 71
            ],
            [
                "giata_id" => 12528742,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "55334",
                "match_percentage" => 55
            ],
            [
                "giata_id" => 13873153,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "150215",
                "match_percentage" => 93
            ],
            [
                "giata_id" => 16160582,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "51202",
                "match_percentage" => 100
            ],
            [
                "giata_id" => 16205015,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "51181",
                "match_percentage" => 71
            ],
            [
                "giata_id" => 17314295,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "54134",
                "match_percentage" => 94
            ],
            [
                "giata_id" => 18231804,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "54129",
                "match_percentage" => 66
            ],
            [
                "giata_id" => 18774844,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "124900",
                "match_percentage" => 100
            ],
            [
                "giata_id" => 19136364,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "148479",
                "match_percentage" => 100
            ],
            [
                "giata_id" => 27011324,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "55393",
                "match_percentage" => 51
            ],
            [
                "giata_id" => 27878720,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "56404",
                "match_percentage" => 72
            ],
            [
                "giata_id" => 31009302,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "90606",
                "match_percentage" => 64
            ],
            [
                "giata_id" => 31444695,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "77405",
                "match_percentage" => 100
            ],
            [
                "giata_id" => 40995724,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "145247",
                "match_percentage" => 72
            ],
            [
                "giata_id" => 42851280,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "125489",
                "match_percentage" => 86
            ],
            [
                "giata_id" => 47668397,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "49810",
                "match_percentage" => 92
            ],
            [
                "giata_id" => 47860146,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "123038",
                "match_percentage" => 100
            ],
            [
                "giata_id" => 49643280,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "87130",
                "match_percentage" => 100
            ],
            [
                "giata_id" => 54047804,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "86239",
                "match_percentage" => 70
            ],
            [
                "giata_id" => 60127313,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "52540",
                "match_percentage" => 90
            ],
            [
                "giata_id" => 63041004,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "123215",
                "match_percentage" => 85
            ],
            [
                "giata_id" => 68565906,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "135894",
                "match_percentage" => 94
            ],
            [
                "giata_id" => 71545713,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "70127",
                "match_percentage" => 100
            ],
            [
                "giata_id" => 75911233,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "98894",
                "match_percentage" => 100
            ],
            [
                "giata_id" => 76354011,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "147839",
                "match_percentage" => 96
            ],
            [
                "giata_id" => 81342626,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "55339",
                "match_percentage" => 63
            ],
            [
                "giata_id" => 83268706,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "126571",
                "match_percentage" => 69
            ],
            [
                "giata_id" => 85422844,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "83880",
                "match_percentage" => 57
            ],
            [
                "giata_id" => 90511746,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "55396",
                "match_percentage" => 59
            ],
            [
                "giata_id" => 93312535,
                "supplier" => MappingSuppliersEnum::IcePortal->value,
                "supplier_id" => "61680",
                "match_percentage" => 78
            ]
        ];
    }
}
