# Performance Improvement Measures for Search Price Cache Cleaning of Recently Booked Hotel Rooms

## Overview

This upgrade includes three stages:

1. Splitting the `api_booking_item` table into two: `api_booking_item` and `api_booking_item_cache`.
2. Removing direct dependencies on `room_id` and `hotel_id`.
3. Implementing a more efficient caching mechanism for booked rooms.

Previously, there was a single table, `api_booking_item`. Now, it is split into:

- `api_booking_item_cache` – Stores preliminary or temporary records with a lifetime equal to the search query cache.
- `api_booking_item` – Stores records that have been added to the cart, meaning they are included in the booking flow.

## Changes

### 1. Separation of `api_booking_item` and `api_booking_item_cache`

- `api_booking_item_cache` will store all booking items generated during a search.
- When a user adds items to the cart, only those items will be moved from `api_booking_item_cache` to `api_booking_item`.
- `api_booking_item_cache` entries are temporary and are automatically cleared based on cache expiration.

#### Benefits:

1. Prevents `api_booking_item` from accumulating unnecessary records, improving query performance.
2. `api_booking_item_cache` enables easy cleanup of unused booking items created during searches, reducing database load.

A scheduled task has been added to purge expired booking items from `api_booking_item_cache`:

```cron
Schedule::command('purge-booking-item-cache')->cron('0 * * * *');
```

### 2. Removal of `room_id` and `hotel_id` Fields

- The fields `room_id` and `hotel_id` have been removed from `api_booking_item_cache` and `api_booking_item`.
- Instead, a new field `cache_checkpoint` is used.

#### Benefits:

1. Makes the entities more universal and independent of the type (hotel, tour, transfer, etc.).

### 3. Improved Caching Approach for Booked Rooms

- A new caching mechanism has been implemented to optimize booked room caching and search cache clearing.

#### Benefits:

1. Significantly reduces database query execution time for processes and endpoints.
2. Reduces the load on the database.

## Conclusion

This upgrade enhances system performance by minimizing unnecessary database records, improving cache management, and reducing query execution time.

