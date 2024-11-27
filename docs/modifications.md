
# Booking Change API Documentation

[Back to Main README](../README.md)

## Definitions

- **Soft Change**: Changes to a booking that do not affect the price.
- **Hard Change**: Changes to a booking that may impact the cost.

## Advantages of Hard Change

Using a hard change can help avoid the complete cancellation of a booking, preserve the last available room, and maintain the original price when possible.

## Available Endpoints

This endpoint provides a list of all of the endpoints available for use on a booking item.

### Endpoint: `api/booking/change/available-endpoints`

- **Method**: GET

### Example Request

```http
GET api/booking/change/available-endpoints/?booking_item={{booking_item}}
```

### Example Response

```json
{
   "success": true,
   "data": {
       "booking_item": "e977c4bd-a3a4-48be-b2d1-12e1e46986a6",
       "supplier": "HBSI",
       "endpoints": [
           {
               "name": "Soft Change",
               "description": "Endpoint to handle soft changes in booking.",
               "url": "api/booking/change/soft-change"
           },
           {
               "name": "Availability Check",
               "description": "Endpoint to check booking availability.",
               "url": "api/booking/change/availability"
           },
           {
               "name": "Price Check",
               "description": "Endpoint to check the price of bookings.",
               "url": "api/booking/change/price-check"
           },
           {
               "name": "Hard Change",
               "description": "Endpoint to handle hard changes in booking.",
               "url": "api/booking/change/hard-change"
           }
       ]
   },
   "message": "success"
}
```

## Soft Change

Soft changes include modifications such as:

- `title`
- `given_name`
- `family_name`
- `special_request`

### Endpoint: `api/booking/change/soft-change`

- **Method**: PUT

### Example Request

```json
{
    "booking_id": "99a2c624-1280-4adf-8155-67afaae7982e",
    "booking_item": "e0f8eaa6-2190-4d83-a52b-fad8ecb70033",
    "passengers": [
        {
            "title": "mr",
            "given_name": "Lonnys",
            "family_name": "Qwerty",
            "room": 1
        },
        {
            "title": "mr",
            "given_name": "Lonnys",
            "family_name": "Qwerty",
            "room": 1
        },
        {
            "title": "mr",
            "given_name": "Passengers1",
            "family_name": "Qwerty",
            "room": 2
        }
    ],
    "special_requests": [
       {
           "room": 1,
           "special_request": "We're celebrating; any chance for a room upgrade or special amenities?"
       }
    ]
}
```

### Example Response

```json
{
   "success": true,
   "data": {
       "status": "Booking changed."
   },
   "message": "success"
}
```

## Hard Change

A hard change involves a three-step process:

### 1. Check Available Options for Desired Changes

At this stage, we can look at the options available for replacement within the original hotel and select a replacement option.

#### Endpoint: `api/booking/change/availability`

- **Method**: POST

#### Example Request

```json
{
   "booking_id": "81a25d1e-d06d-4772-89be-b0132bdbbe48",
   "booking_item": "08d528c6-f078-465b-bd29-a0ce210ee376",
   "checkin": "2024-12-15",
   "checkout": "2024-12-17",
   "occupancy": [
       {
           "adults": 2,
           "children_ages": [
               2,
               5
           ]
       },
       {
           "adults": 3
       }
   ]
}
```

#### Response

Similar to pricing search response.

### 2. Check Price Changes

At this stage, we can see the possible cost escalation of changing the booking and the cancellation policy for the original booking.

#### Endpoint: `api/booking/change/price-check`

- **Method**: GET

#### Example Request

```http
GET api/booking/change/price-check/?new_booking_item=&booking_id=99a2c624-1280-4adf-8155-67afaae7982e&booking_item=fa45816f-7183-4545-bfe4-8fa8ec0d20e1
```

#### Example Response

```json
{
    "success": true,
    "data": {
        "result": {
            "incremental_total_price": 1950,
            "current_booking_item": {
                "total_net": 750,
                "total_tax": 0,
                "total_fees": 0,
                "total_price": 750,
                "cancellation_policies": [
                    {
                        "type": "General",
                        "percentage": "100",
                        "description": "General Cancellation Policy",
                        "penalty_start_date": "2024-08-01"
                    }
                ],
                "breakdown": [
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 227.27
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "22.73"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 227.27
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "22.73"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": "0.00"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 227.27
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "22.73"
                        }
                    ]
                ],
                "rate_name": "Promo",
                "room_name": "Suite",
                "currency": "USD",
                "booking_item": "326765fc-44fc-44a9-8d22-875bfbc27d04",
                "hotelier_booking_reference": "721fKAwOzg"
            },
            "new_booking_item": {
                "total_net": 2700,
                "total_tax": 0,
                "total_fees": 0,
                "total_price": 2700,
                "cancellation_policies": [
                    {
                        "type": "General",
                        "percentage": "20",
                        "description": "General Cancellation Policy",
                        "penalty_start_date": "2024-12-01"
                    },
                    {
                        "type": "General",
                        "percentage": "50",
                        "description": "General Cancellation Policy",
                        "penalty_start_date": "2024-12-08"
                    }
                ],
                "breakdown": [
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title":

 "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "title": "Base Rate",
                            "amount": 272.73
                        },
                        {
                            "type": "tax",
                            "title": "Occupancy Tax",
                            "amount": "27.27"
                        }
                    ]
                ],
                "rate_name": "Best",
                "room_name": "Double",
                "currency": "USD",
                "booking_item": "326765fc-44fc-44a9-8d22-875bfbc27d04"
            }
        }
    },
    "message": "success"
}
```

### 3. Confirm/Execute Hard Change

At this stage, we confirm/agree to the terms and conditions of a possible increase in the cost of the booking change and carry out the process of hard change the booking.

#### Endpoint: `api/booking/change/hard-change`

- **Method**: PUT

#### Example Request

```json
{
    "new_booking_item": "e51aae26-4c67-4a7d-8b5d-6bea12b3e61d",
    "booking_id": "99a2c624-1280-4adf-8155-67afaae7982e",
    "booking_item": "fa45816f-7183-4545-bfe4-8fa8ec0d20e1",
    "passengers": [
        {
            "title": "mr",
            "given_name": "Lonnys",
            "family_name": "Qwerty",
            "date_of_birth": "1969-01-24",
            "room": 1
        },
        {
            "title": "mr",
            "given_name": "Lonnys",
            "family_name": "Qwerty",
            "date_of_birth": "1974-07-04",
            "room": 1
        }
    ],
    "special_requests": [
       {
           "room": 1,
           "special_request": "We're celebrating; any chance for a room upgrade or special amenities?"
       }
    ]
}
```

#### Example Response

```json
{
   "success": true,
   "data": {
       "status": "Booking changed."
   },
   "message": "success"
}
```

## Hard Change Flow Prerequisites

The Hard Change API is designed based on the Expedia Hard Change API functionality:

1. **Shop for Change** - Endpoint: `api/booking/change/availability`
2. **Price Check** - Endpoint: `api/booking/change/price-check`
3. **Commit Change** - Endpoint: `api/booking/change/hard-change`

**Itinerary Retrieval** (already available in our system) - Endpoint: `api/booking/retrieve-booking?booking_id={{booking_id}}`

Thus, the endpoints are universal for different providers.
```
