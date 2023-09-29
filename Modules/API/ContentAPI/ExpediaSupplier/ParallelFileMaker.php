<?php

namespace Modules\API\ContentAPI\ExpediaSupplier;

use GuzzleHttp\Client;

class ParallelFileMaker
{
    private const APIKEY = env('EXPEDIA_RAPID_API_KEY');

    private const SHARED_SECRET = env('EXPEDIA_RAPID_SHARED_SECRET');
    private const COUNTRIES = ["AD", "AE", "AF", "AG", "AI", "AL", "AM", "AO", "AQ",
        "AR", "AS", "AT", "AU", "AW", "AX", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BL", "BM",
        "BN", "BO", "BQ", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD", "CF", "CG", "CH", "CI", "CK",
        "CL", "CM", "CN", "CO", "CR", "CU", "CV", "CW", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC",
        "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "GA", "GB", "GD", "GE", "GF", "GG",
        "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT", "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT",
        "HU", "ID", "IE", "IL", "IM", "IN", "IO", "IQ", "IR", "IS", "IT", "JE", "JM", "JO", "JP", "KE", "KG", "KH",
        "KI", "KM", "KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT", "LU", "LV",
        "LY", "MA", "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML", "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT",
        "MU", "MV", "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ",
        "OM", "PA", "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY", "QA", "RE", "RO",
        "RS", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO", "SR",
        "SS", "ST", "SV", "SX", "SY", "SZ", "TC", "TD", "TF", "TG", "TH", "TJ", "TK", "TL", "TM", "TN", "TO", "TR",
        "TT", "TV", "TW", "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN", "VU", "WF",
        "WS", "YE", "YT", "ZA", "ZM", "ZW"];
    private const PROPERTY_CATEGORIES = ["0", "1", "2", "3", "4", "5", "6", "7", "8",
        "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26",
        "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44"];
    private const MAX_CALL_SIZE = 20000;
    private const LANGUAGE = "en-US";
    private const SUPPLY_SOURCE = "expedia";

    private $RAPID_CLIENT;

    public function __construct ()
    {
        $this->RAPID_CLIENT = new RapidClient(self::APIKEY, self::SHARED_SECRET);
    }

    public function run ()
    {
        $allCalls = $this->divideUpCalls();

        // Make sure we're making the calls in the most efficient order.
        $callsToMake = array_map(
            function ($entry) {
                return $entry['call'];
            },
            array_filter(
                $allCalls,
                function ($entry) {
                    return $entry['size'] > 0;
                }
            )
        );

        // Combine all the streams into one big stream and actually make the calls and write to the file.
        $outputFileWriter = $this->createFileWriter('output.jsonl.gz');

        parallel_map(
            function ($property) use ($outputFileWriter) {
                try {
                    // Write to output file
                    flock($outputFileWriter, LOCK_EX);
                    fwrite($outputFileWriter, json_encode($property) . "\n");
                    flock($outputFileWriter, LOCK_UN);
                } catch (Exception $e) {
                    // Handle exception
                }
            },
            $this->combineStreams($callsToMake)
        );

        fclose($outputFileWriter);
    }

    private function divideUpCalls ()
    {
        $allCalls = [];

        parallel_map(
            function ($countryCode) use (&$allCalls) {
                $countryCall = new PropertyContentCall(
                    $this->RAPID_CLIENT,
                    self::LANGUAGE,
                    self::SUPPLY_SOURCE,
                    [$countryCode],
                    null
                );
                $countryCallSize = $countryCall->size();

                if ($countryCallSize < self::MAX_CALL_SIZE) {
                    // It's small enough! No need to break this call up further.
                    $allCalls[] = ['call' => $countryCall, 'size' => $countryCallSize];
                } else {
                    // The country is too big, need to break up the call into smaller parts.
                    parallel_map(
                        function ($category) use (&$allCalls, $countryCode) {
                            // Exclude every category except the current one, so it's as if we're searching
                            // for only the current category.
                            $excludedCategories = array_diff(self::PROPERTY_CATEGORIES, [$category]);
                            $categoryCall = new PropertyContentCall(
                                $this->RAPID_CLIENT,
                                self::LANGUAGE,
                                self::SUPPLY_SOURCE,
                                [$countryCode],
                                $excludedCategories
                            );

                            $allCalls[] = ['call' => $categoryCall, 'size' => $categoryCall->size()];
                        },
                        self::PROPERTY_CATEGORIES
                    );
                }
            },
            self::COUNTRIES
        );

        return $allCalls;
    }

    private function combineStreams ($streams)
    {
        $combinedStream = null;

        foreach ($streams as $stream) {
            if (!is_null($stream)) {
                if (is_null($combinedStream)) {
                    $combinedStream = $stream;
                } else {
                    $combinedStream = array_merge($combinedStream, $stream);
                }
            }
        }

        return $combinedStream;
    }

    private function createFileWriter ($path)
    {
        $outputFileWriter = gzopen($path, 'w');
        stream_set_blocking($outputFileWriter, 1); // Enable blocking for flock

        return $outputFileWriter;
    }
}

# Example usage:
// $parallelFileMaker = new ParallelFileMaker();
// $parallelFileMaker->run();
