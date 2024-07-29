<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiataProperty>
 */
class GiataPropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $addressLine = "Delhy No. {$this->faker->numberBetween(1, 100)}, Viln {$this->faker->word()}";

        return [
            'code' => $this->faker->numberBetween(1, 100000),
            'last_updated' => $this->faker->dateTimeThisDecade(),
            'name' => $this->faker->name(),
            'chain' => '{}',
            'city' => $this->faker->city(),
            'city_id' => $this->faker->numberBetween(1, 100000),
            'locale' => $this->faker->locale(),
            'address' => '{
                "CityName": "New Delhi",
                "AddressLine": "'.$addressLine.'",
                "PostalCode": "110037",
                "@attributes": {
                    "UseType": "7",
                    "FormattedInd": "true",
                },
                "CountryName": "IN"
            }',
            'mapper_phone_number' => $this->faker->phoneNumber(),
            'mapper_address' => $addressLine,
            'phone' => [
                '{
                    "@attributes": {
                        "PhoneNumber": "+911171558800",
                        "PhoneTechType": "1"
                    }
                }',
                '{
                    "@attributes": {
                        "PhoneNumber": '.$this->faker->phoneNumber().',
                        "PhoneTechType": "3"
                    }
                }',
            ],
            'position' => '{
                "@attributes": {
                    "Latitude": "28.550831",
                    "Longitude": "77.120576",
                    "PositionAccuracy": "1"
                }
            }',
            'latitude' => $this->faker->randomFloat(4, -90, 90),
            'longitude' => $this->faker->randomFloat(3, -180, 180),
            'url' => '{}',
            'cross_references' => '[
                {
                    "Code": {
                        "@attributes": {
                            "HotelCode": "431765"
                        }
                    },
                    "@attributes": {
                        "Code": "GIATA-ID",
                        "Name": "GIATA-ID",
                        "Type": "10"
                    }
                },
                {
                    "Code": [
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000"
                            }
                        },
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000-0243-TF"
                            }
                        },
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000-0243-TS"
                            }
                        }
                    ],
                    "@attributes": {
                        "Code": "TUID",
                        "Name": "TUI",
                        "Type": "6"
                    }
                },
                {
                    "Code": [
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000"
                            }
                        },
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000-0030-TF"
                            }
                        }
                    ],
                    "@attributes": {
                        "Code": "ATID",
                        "Name": "airtours",
                        "Type": "6"
                    }
                },
                {
                    "Code": {
                        "@attributes": {
                            "HotelCode": "Y01PE6"
                        }
                    },
                    "@attributes": {
                        "Code": "AME",
                        "Name": "Ameropa-Reisen",
                        "Type": "6"
                    }
                },
                {
                    "Code": [
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000"
                            }
                        },
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000-0471-TF"
                            }
                        },
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000-0471-TS"
                            }
                        }
                    ],
                    "@attributes": {
                        "Code": "TUIS",
                        "Name": "TUI Suisse Ltd",
                        "Type": "6"
                    }
                },
                {
                    "Code": [
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000"
                            }
                        },
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000-0222-TF"
                            }
                        }
                    ],
                    "@attributes": {
                        "Code": "XTUI",
                        "Name": "XTUI",
                        "Type": "6"
                    }
                },
                {
                    "Code": {
                        "@attributes": {
                            "HotelCode": "179072"
                        }
                    },
                    "@attributes": {
                        "Code": "SUNH",
                        "Name": "SunHotels",
                        "Type": "6"
                    }
                },
                {
                    "Code": {
                        "@attributes": {
                            "HotelCode": "DELD0314"
                        }
                    },
                    "@attributes": {
                        "Code": "XEUR",
                        "Name": "Eurotours",
                        "Type": "6"
                    }
                },
                {
                    "Code": [
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000"
                            }
                        },
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000-0454-TF"
                            }
                        },
                    ],
                    "@attributes": {
                        "Code": "ATIS",
                        "Name": "airtours Suisse",
                        "Type": "6"
                    }
                },
                {
                    "Code": [
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000"
                            }
                        },
                        {
                            "@attributes": {
                                "HotelCode": "DEL30000-0617-TF"
                            }
                        },

                    ],
                    "@attributes": {
                        "Code": "TUR1",
                        "Name": "ltur GmbH (Tui Gruppe)",
                        "Type": "6"
                    }
                },
                {
                    "Code": {
                        "@attributes": {
                            "HotelCode": "DELD0279"
                        }
                    },
                    "@attributes": {
                        "Code": "FIB",
                        "Name": "Fibula Travel GmbH",
                        "Type": "6"
                    }
                }
            ]',
            'created_at' => $this->faker->dateTimeThisDecade(), // Пример значения для created_at
            'updated_at' => $this->faker->dateTimeThisDecade(), // Пример значения для updated_at
        ];
    }
}
