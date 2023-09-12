<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExpediaContentMain;
use App\Models\ExpediaContentSlave;


class ImportJsonlDataWithPartitions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected const BATCH_SIZE = 100;
	protected $signature = 'import:jsonl-with-partitions {file}';
	protected $description = 'Import JSONL data into the database with partitions';


    /**
     * Execute the console command.
     */
    public function handle()
	{
		$filePath = $this->argument('file');

		// Open the JSONL file for reading
		$file = fopen($filePath, 'r');

		if (!$file) {
			$this->error('Unable to open the JSONL file.');
			return;
		}

		$batchSize = self::BATCH_SIZE; // Set your desired batch size
		$batchDataMain = [];
		$batchCountMain = 0;
		$batchDataSlave = [];
		$batchCountSlave = 0;
		$arr_json_main = [
			'address', 'ratings', 'location'
		];
		$arr_json_slave = [
			'category', 'business_model',
			'checkin', 'checkout', 'fees', 'policies', 'attributes', 'amenities',
			'images', 'onsite_payments', 'rooms', 'rates', 'dates', 'descriptions',
			'themes', 'chain', 'brand', 'statistics', 'vacation_rental_details',
			'airports', 'fax', 'spoken_languages', 'all_inclusive'
		];
		$arr_main = [
			'property_id', 'name'
		];
		$arr_slave = [
			'property_id', 'phone', 'tax_id', 'rank', 'multi_unit',
			'payment_registration_recommended', 'supply_source'
		];
		$start_time = microtime(true);

		while (($line = fgets($file)) !== false) {
			// Parse JSON from each line
			$data = json_decode($line, true);

			$output_main = [];
			foreach ($arr_json_main as $key) {
				$output_main[$key] = json_encode(['']);
			}
			foreach ($arr_main as $key) {
				$output_main[$key] = '';
			}
			$output_slave = [];
			foreach ($arr_json_slave as $key) {
				$output_slave[$key] = json_encode(['']);
			}
			foreach ($arr_slave as $key) {
				$output_slave[$key] = '';
			}
			

			if (!is_array($data)) break;
			
			foreach ($data as $key => $value) {
				
				if ($key == 'ratings') {
					$output_main['rating'] = $value['property']['rating'] ?? 0;
				}
				if ($key == 'address') {
					$country_code = $value['country_code'];
					$country_hash = $this->getKey($country_code);
					$output_slave['country_hash'] = $country_hash;
					$output_main['country_hash'] = $country_hash;
				}
				if ($key == 'location') {
					$output_main['latitude'] = $value['coordinates']['latitude'] ?? 0;
					$output_main['longitude'] = $value['coordinates']['longitude'] ?? 0;
				}
				if (in_array($key, $arr_main) || in_array($key, $arr_json_main)) {
					if (is_array($value)) {
						$value = json_encode($value);
					}
					$output_main[$key] = $value;
				} 
				if (in_array($key, $arr_slave) || in_array($key, $arr_json_slave)) {
					if (is_array($value)) {
						$value = json_encode($value);
					}
					$output_slave[$key] = $value;
				}
			}

			if (!isset($output_main['latitude'])) {
				$output_main['latitude'] = 0;
			}
			if (!isset($output_main['longitude'])) {
				$output_main['longitude'] = 0;
			}
			if (!isset($output_main['rating'])) {
				$output_main['rating'] = 0;
			}

			$batchDataMain[] = $output_main;
			$batchDataSlave[] = $output_slave;

			if (count($batchDataMain) >= $batchSize) {
				try {
					ExpediaContentMain::insert($batchDataMain);
				} catch (\Exception $e) {
					\Log::error('ImportJsonlData', ['error' => $e->getMessage()]);
				}
				
				$batchCountMain++;
				$this->info('Data imported batchDataMain: ' . $batchCountMain. ' count =  ' . count($batchDataMain));
				$batchDataMain = [];
			}
			if (count($batchDataSlave) >= $batchSize) {
				try {
					ExpediaContentSlave::insert($batchDataSlave);
				} catch (\Exception $e) {
					\Log::error('ImportJsonlData', ['error' => $e->getMessage()]);
				}
				
				$batchCountSlave++;
				$this->info('Data imported batchDataSlave: ' . $batchCountSlave. ' count =  ' . count($batchDataSlave));
				$batchDataSlave = [];
			}
			
		}

		if (!empty($batchDataMain)) {
			try {
				ExpediaContentMain::insert($batchDataMain);
			} catch (\Exception $e) {
				\Log::error('ImportJsonlData', ['error' => $e->getMessage()]);
			}
		}
		if (!empty($batchDataSlave)) {
			try {
				ExpediaContentSlave::insert($batchDataSlave);
			} catch (\Exception $e) {
				\Log::error('ImportJsonlData', ['error' => $e->getMessage()]);
			}
		}

		fclose($file);
		$end_time = microtime(true);
		$execution_time = ($end_time - $start_time);
		$this->info('Import completed. ' . round($execution_time, 2) . " seconds");		
	}

	static function getKey($counrty)
		{
			$arr = [
				'AD' => 1,
				'AE' => 2,
				'AF' => 3,
				'AG' => 4,
				'AI' => 5,
				'AL' => 6,
				'AM' => 7,
				'AO' => 8,
				'AQ' => 9,
				'AR' => 10,
				'AS' => 11,
				'AT' => 12,
				'AU' => 13,
				'AW' => 14,
				'AX' => 15,
				'AZ' => 16,
				'BA' => 17,
				'BB' => 18,
				'BD' => 19,
				'BE' => 20,
				'BF' => 21,
				'BG' => 22,
				'BH' => 23,
				'BI' => 24,
				'BJ' => 25,
				'BL' => 26,
				'BM' => 27,
				'BN' => 28,
				'BO' => 29,
				'BQ' => 30,
				'BR' => 31,
				'BS' => 32,
				'BT' => 33,
				'BV' => 34,
				'BW' => 35,
				'BY' => 36,
				'BZ' => 37,
				'CA' => 38,
				'CC' => 39,
				'CD' => 40,
				'CF' => 41,
				'CG' => 42,
				'CH' => 43,
				'CI' => 44,
				'CK' => 45,
				'CL' => 46,
				'CM' => 47,
				'CN' => 48,
				'CO' => 49,
				'CR' => 50,
				'CU' => 51,
				'CV' => 52,
				'CW' => 53,
				'CX' => 54,
				'CY' => 55,
				'CZ' => 56,
				'DE' => 57,
				'DJ' => 58,
				'DK' => 59,
				'DM' => 60,
				'DO' => 61,
				'DZ' => 62,
				'EC' => 63,
				'EE' => 64,
				'EG' => 65,
				'EH' => 66,
				'ER' => 67,
				'ES' => 68,
				'ET' => 69,
				'FI' => 70,
				'FJ' => 71,
				'FK' => 72,
				'FM' => 73,
				'FO' => 74,
				'FR' => 75,
				'GA' => 76,
				'GB' => 77,
				'GD' => 78,
				'GE' => 79,
				'GF' => 80,
				'GG' => 81,
				'GH' => 82,
				'GI' => 83,
				'GL' => 84,
				'GM' => 85,
				'GN' => 86,
				'GP' => 87,
				'GQ' => 88,
				'GR' => 89,
				'GS' => 90,
				'GT' => 91,
				'GU' => 92,
				'GW' => 93,
				'GY' => 94,
				'HK' => 95,
				'HM' => 96,
				'HN' => 97,
				'HR' => 98,
				'HT' => 99,
				'HU' => 100,
				'ID' => 101,
				'IE' => 102,
				'IL' => 103,
				'IM' => 104,
				'IN' => 105,
				'IO' => 106,
				'IQ' => 107,
				'IR' => 108,
				'IS' => 109,
				'IT' => 110,
				'JE' => 111,
				'JM' => 112,
				'JO' => 113,
				'JP' => 114,
				'KE' => 115,
				'KG' => 116,
				'KH' => 117,
				'KI' => 118,
				'KM' => 119,
				'KN' => 120,
				'KP' => 121,
				'KR' => 122,
				'KW' => 123,
				'KY' => 124,
				'KZ' => 125,
				'LA' => 126,
				'LB' => 127,
				'LC' => 128,
				'LI' => 129,
				'LK' => 130,
				'LR' => 131,
				'LS' => 132,
				'LT' => 133,
				'LU' => 134,
				'LV' => 135,
				'LY' => 136,
				'MA' => 137,
				'MC' => 138,
				'MD' => 139,
				'ME' => 140,
				'MF' => 141,
				'MG' => 142,
				'MH' => 143,
				'MK' => 144,
				'ML' => 145,
				'MM' => 146,
				'MN' => 147,
				'MO' => 148,
				'MP' => 149,
				'MQ' => 150,
				'MR' => 151,
				'MS' => 152,
				'MT' => 153,
				'MU' => 154,
				'MV' => 155,
				'MW' => 156,
				'MX' => 157,
				'MY' => 158,
				'MZ' => 159,
				'NA' => 160,
				'NC' => 161,
				'NE' => 162,
				'NF' => 163,
				'NG' => 164,
				'NI' => 165,
				'NL' => 166,
				'NO' => 167,
				'NP' => 168,
				'NR' => 169,
				'NU' => 170,
				'NZ' => 171,
				'OM' => 172,
				'PA' => 173,
				'PE' => 174,
				'PF' => 175,
				'PG' => 176,
				'PH' => 177,
				'PK' => 178,
				'PL' => 179,
				'PM' => 180,
				'PN' => 181,
				'PR' => 182,
				'PS' => 183,
				'PT' => 184,
				'PW' => 185,
				'PY' => 186,
				'QA' => 187,
				'RE' => 188,
				'RO' => 189,
				'RS' => 190,
				'RU' => 191,
				'RW' => 192,
				'SA' => 193,
				'SB' => 194,
				'SC' => 195,
				'SD' => 196,
				'SE' => 197,
				'SG' => 198,
				'SH' => 199,
				'SI' => 200,
				'SJ' => 201,
				'SK' => 202,
				'SL' => 203,
				'SM' => 204,
				'SN' => 205,
				'SO' => 206,
				'SR' => 207,
				'SS' => 208,
				'ST' => 209,
				'SV' => 210,
				'SX' => 211,
				'SY' => 212,
				'SZ' => 213,
				'TC' => 214,
				'TD' => 215,
				'TF' => 216,
				'TG' => 217,
				'TH' => 218,
				'TJ' => 219,
				'TK' => 220,
				'TL' => 221,
				'TM' => 222,
				'TN' => 223,
				'TO' => 224,
				'TR' => 225,
				'TT' => 226,
				'TV' => 227,
				'TW' => 228,
				'TZ' => 229,
				'UA' => 230,
				'UG' => 231,
				'UM' => 232,
				'US' => 233,
				'UY' => 234,
				'UZ' => 235,
				'VA' => 236,
				'VC' => 237,
				'VE' => 238,
				'VG' => 239,
				'VI' => 240,
				'VN' => 241,
				'VU' => 242,
				'WF' => 243,
				'WS' => 244,
				'YE' => 245,
				'YT' => 246,
				'ZA' => 247,
				'ZM' => 248,
				'ZW' => 249,
			];
			return $arr[$counrty];
		}
}
