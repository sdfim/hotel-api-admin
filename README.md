# TABLE OF CONTENTS

1. [API Documentation](#API-Documentation)
    - [Search Endpoint](#1-Search-Endpoint)
    - [Add Item Endpoint](#2-Add-Item-Endpoint)
    - [Retrieve Items Endpoint](#3-Retrieve-Items-Endpoint)
    - [Remove Item Endpoint](#4-Remove-Item-Endpoint)
    - [Add Passengers Endpoint](#5-Add-Passengers-Endpoint)
    - [Book Endpoint](#6-Book-Endpoint)
2. [Important Testing Notes](#Important-Testing-Notes)
3. [Local Installation Instructions](docs/local-installation.md)

## API Documentation

____
[:arrow_up:TABLE OF CONTENTS](#table-of-contents)
____

### 1. Search Endpoint

**Endpoint:** `api/content/search`

**Method:** `POST`

**Request Body:**

```json
{
    "type": "hotel",
    "checkin": "2023-12-15",
    "checkout": "2023-12-25",
    "destination": 961,
    "occupancy": [
        {
            "adults": 2
        }
    ]
}
```

**Optional Parameters:**

- `supplier`: The supplier name (e.g., "Expedia").
- `currency`: The currency code (e.g., "USD").
- `rating`: The minimum hotel rating (e.g., 4.0).

**Multiple Rooms:**

```json
{
    "occupancy": [
        {
            "adults": 2,
            "children_ages": [
                2,
                2
            ]
        },
        {
            "adults": 3
        },
        {
            "adults": 1,
            "children_ages": [
                2,
                0
            ]
        }
    ]
}
```

**Response:**

The response contains the `search_id` and `booking_item` which can be used in subsequent endpoints.

____
[:arrow_up:TABLE OF CONTENTS](#table-of-contents)
____

### 2. Add Item Endpoint

**Endpoint:** `api/booking/add-item?booking_item={booking_item}`

**Method:** `POST`

**Optional Parameters:**

- `booking_id`: The ID of the booking to which the item should be added.
- If we execute such a request without specifying the booking_id, then a new cart will be created, and we will receive a
  booking_id.

- To put a booking_item into an existing cart (booking_id) in this endpoint, we need to indicate the number of
  the cart in which we want to put the item
  api/booking/add-item?booking_item={booking_item}&booking_id={booking_id}
  This will add another item to the existing cart

____
[:arrow_up:TABLE OF CONTENTS](#table-of-contents)
____

### 3. Retrieve Items Endpoint

**Endpoint:** `api/booking/retrieve-items?booking_id={booking_id}`

**Method:** `GET`

- To see what is in a specific basket, we use an endpoint api/booking/retrieve-items
- At this point, we can check whether Passengers are added for all booking_item.

____
[:arrow_up:TABLE OF CONTENTS](#table-of-contents)
____

### 4. Remove Item Endpoint

**Endpoint:** `api/booking/remove-item?booking_id={booking_id}&booking_item={booking_item}`

**Method:** `DELETE`

- Using this endpoint, we can remove any item from the cart

____
[:arrow_up:TABLE OF CONTENTS](#table-of-contents)
____

### 5. Add Passengers Endpoint

**Endpoint:** `api/booking/add-passengers?booking_id={booking_id}`

**Method:** `POST`

**Request Body:**

- In the example above, we searched for three rooms.
- In the first room we have two adults and two children.
- In the second room, there are three adults.
- In the third room we have one adult and two children.

- in this case, adding passengers for such an item may look like this:

```json
{
    "passengers": [
        {
            "title": "mr",
            "given_name": "Adult_1",
            "family_name": "Gutkowski",
            "date_of_birth": "1977-09-17",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 1
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Adult_2",
            "family_name": "Jacobs",
            "date_of_birth": "1980-08-23",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 1
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Adult_1",
            "family_name": "Murray",
            "date_of_birth": "1966-07-23",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 2
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Adult_2",
            "family_name": "Cormier",
            "date_of_birth": "1972-04-12",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 2
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Adult_3",
            "family_name": "Tillman",
            "date_of_birth": "1967-03-25",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 2
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Adult_1",
            "family_name": "Beahan",
            "date_of_birth": "1978-10-26",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 3
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Children_1",
            "family_name": "Langosh",
            "date_of_birth": "2021-11-06",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 1
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Children_2",
            "family_name": "Langosh",
            "date_of_birth": "2021-10-06",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 1
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Children_1",
            "family_name": "Langosh",
            "date_of_birth": "2021-11-06",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 3
                }
            ]
        },
        {
            "title": "mr",
            "given_name": "Children_2",
            "family_name": "Langosh",
            "date_of_birth": "2023-01-06",
            "booking_items": [
                {
                    "booking_item": "26d22cfa-1456-40fb-b88f-9bd6300c4d9e",
                    "room": 3
                }
            ]
        }
    ]
}

```

**Notes:**

- The number of adults, number of children, and the age of children when adding passengers must correspond to the
  parameters specified during the search.
- The same passenger can be in different rooms.
- The same passenger can be in different booking items.
- The endpoint call can be performed multiple times with the same parameters; in this case, passenger data will be
  updated.
- With one request, you can add passengers for one or several booking items.

____
[:arrow_up:TABLE OF CONTENTS](#table-of-contents)
____

### 6. Book Endpoint

**Endpoint:** `api/booking/book?booking_id={booking_id}`

**Method:** `POST`

**Notes:**

- After you add passengers for all booking items that are in the cart, you can go to this endpoint.
- Use endpoint `api/booking/retrieve-items?booking_id={booking_id}` to check that passengers have been added for all
  booking items.

____
[:arrow_up:TABLE OF CONTENTS](#table-of-contents)
____

## Important Testing Notes

All tests are stored in the `tests` folder, and the necessary variables should be specified in the `.env.testing` file.
If the .env.testing file is not present, the variables from the `.env` file will be used as a reference
(see `.env.example` for guidance).
This file should be created manually if it doesn't exist.

Using `RDS`(or its local copy) for running tests is convenient due to its inclusion of a full dataset.
However, the project has specific considerations for testing.

It's essential to refrain from using `Factories` in tests for `Models`
(tables) located in the second database(`RDS` or its `local copy`).
It is crucial to refrain from using fake data in the tables of the second database(`RDS` or its `local copy`).

Additionally, if the `use RefreshDatabase;` trait is being employed, it is advisable to set up a separate test database 
for the initial connection to avoid losing all data from the local database.
This precaution is necessary because, after all tests are executed, the `php artisan migrate:fresh --seed` 
command is run.

The provided sample should be used when running tests with `RDS` database in the second DB connection:

```dotenv
DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=ujv_test
DB_USERNAME=admin
DB_PASSWORD=mysqlsecretpasswd

SUPPLIER_CONTENT_DB_CONNECTION=mysql2
SUPPLIER_CONTENT_DB_HOST=ujv-rds-db.************.us-east-1.rds.amazonaws.com
SUPPLIER_CONTENT_DB_PORT=3306
SUPPLIER_CONTENT_DB_DATABASE=ujv_api
SUPPLIER_CONTENT_DB_USERNAME=admin
SUPPLIER_CONTENT_DB_PASSWORD=rds-password

#SUPPLIER_CONTENT_DB_CONNECTION=mysql2
#SUPPLIER_CONTENT_DB_HOST=host.docker.internal
#SUPPLIER_CONTENT_DB_PORT=3306
#SUPPLIER_CONTENT_DB_DATABASE=ujv_aws_test
#SUPPLIER_CONTENT_DB_USERNAME=admin
#SUPPLIER_CONTENT_DB_PASSWORD=mysqlsecretpasswd
```

The provided sample should be utilized when running tests with a local copy of the `RDS` 
database in the second database connection:

```dotenv
DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=ujv_test
DB_USERNAME=admin
DB_PASSWORD=mysqlsecretpasswd

#SUPPLIER_CONTENT_DB_CONNECTION=mysql2
#SUPPLIER_CONTENT_DB_HOST=ujv-rds-db.************.us-east-1.rds.amazonaws.com
#SUPPLIER_CONTENT_DB_PORT=3306
#SUPPLIER_CONTENT_DB_DATABASE=ujv_api
#SUPPLIER_CONTENT_DB_USERNAME=admin
#SUPPLIER_CONTENT_DB_PASSWORD=rds-password

SUPPLIER_CONTENT_DB_CONNECTION=mysql2
SUPPLIER_CONTENT_DB_HOST=host.docker.internal
SUPPLIER_CONTENT_DB_PORT=3306
SUPPLIER_CONTENT_DB_DATABASE=ujv_aws_test
SUPPLIER_CONTENT_DB_USERNAME=admin
SUPPLIER_CONTENT_DB_PASSWORD=mysqlsecretpasswd
```
